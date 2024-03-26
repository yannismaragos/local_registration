<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_registration\controller;

use moodleform;
use moodle_url;
use local_registration\controller\Base;
use local_registration\form\registration;
use local_registration\manager;
use local_registration\encryptor;
use local_registration\helper\Router;
use stdClass;
use DateTime;
use html_writer;

/**
 * Form controller class.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Form extends Base {
    /**
     * @var moodleform The Moodle form object.
     */
    protected $mform;

    /**
     * @var Router The Router object.
     */
    protected $router;

    /**
     * Class contructor.
     */
    public function __construct() {
        parent::__construct();
        $this->router = new Router();
        $this->context = 'system';
        $tenantid = optional_param('tenantid', null, PARAM_INT);
        $this->url = new moodle_url('/local/registration/index.php?view=form&tenantid=' . $tenantid);
        $this->pagelayout = 'standard';
    }

    /**
     * Displays the main content of the page.
     *
     * This method is responsible for rendering the main content of the page,
     * which includes handling form submissions and displaying the registration form.
     *
     * @return void
     */
    protected function display_content(): void {
        $actionurl = '';
        $this->mform = new registration($actionurl);

        if ($this->mform) {
            $task = optional_param('task', 'display', PARAM_ALPHAEXT);

            // Handle form logic.
            if ($task === 'submit') {
                $this->submit_form_via_task();
            } else if ($this->mform->is_cancelled()) {
                $this->cancel_form();
            } else if ($data = $this->mform->get_data()) {
                $this->submit_form($data);
            } else {
                $this->display_form();
            }
        }
    }

    /**
     * Submits the form.
     *
     * @return void
     */
    public function submit(): void {
        $this->submit_form_via_task();
    }

    /**
     * Handles form submission via the 'submit' task.
     *
     * This method processes the form submission when the task parameter is set to 'submit'.
     * It updates or adds a record to the 'local_registration' table based on whether it's
     * a new record or an update. It also handles sending confirmation emails and displaying
     * appropriate messages to the user.
     *
     * @return void
     */
    private function submit_form_via_task(): void {
        global $SESSION, $OUTPUT, $PAGE, $CFG;

        $manager = new manager();
        $encryptor = new encryptor(manager::ENCRYPTION_KEY);

        // Check for empty session variable.
        if (empty($SESSION->local_registration)) {
            $this->router->redirect(
                $this->url,
                get_string('emptysessiondata', 'local_registration'),
                null,
                'error'
            );
        }

        $this->display_header();

        // Start box.
        echo $OUTPUT->box_start('generalbox boxwidthnormal');

        // Submit data to 'local_registration' table.
        // Is this a new record, or are we editing an existing record?
        if (!empty($SESSION->local_registration['id'])) {
            $record = new stdClass();
            $record->id = $SESSION->local_registration['id'];
            $record->tenantid = $SESSION->local_registration['tenantid'];
            $record->firstname = $SESSION->local_registration['firstname'];
            $record->lastname = $SESSION->local_registration['lastname'];
            $record->country = $SESSION->local_registration['country'];
            $record->gender = $SESSION->local_registration['gender'];
            $record->position = $SESSION->local_registration['position'];
            $record->domain = $SESSION->local_registration['domain'];
            $record->comments = $SESSION->local_registration['comments'];
            $record->interests = json_encode($SESSION->local_registration['interests']);
            $time = new DateTime('now');
            $record->timemodified = $time->getTimestamp();
            $record->approved = 0;

            $manager->update_registration_record($record);

            // Notify tenant admins.
            $manager->notify_tenants((int) $record->tenantid, manager::USER_UPDATE);

            // Record updated, display message to user.
            echo html_writer::tag('h3',  get_string('thanks') . ", " . $record->firstname);
            echo html_writer::tag('p',  text_to_html(get_string('registrationupdated', 'local_registration')));
        } else {
            if (!$id = $manager->add_registration_record($SESSION->local_registration)) {
                $this->router->redirect(
                    new moodle_url('/'),
                    get_string('erroremailexists', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Encrypt the email.
            $email = $SESSION->local_registration['email'];
            $hash = $encryptor->encrypt($email);

            // Send confirmation email.
            $confirmurl = new moodle_url('/local/registration/index.php?view=confirm&id=' . $id . '&hash=' . urlencode($hash));
            $manager->send_confirmation_email($SESSION->local_registration, $confirmurl);
            $unconfirmedhours = get_config('local_registration', 'unconfirmedhours');

            // Confirmation email sent, display message to user.
            $PAGE->set_title(get_string('confirmationemailsent', 'local_registration'));
            $PAGE->set_heading(get_string('confirmationemailsent', 'local_registration'));

            echo html_writer::tag('h3',  get_string('thanks') . ", " . $SESSION->local_registration['firstname']);
            echo html_writer::tag('p',  text_to_html(get_string('registrationstarted', 'local_registration', $unconfirmedhours)));
        }

        echo $OUTPUT->single_button("$CFG->wwwroot/", get_string('continue'));

        // End box.
        echo $OUTPUT->box_end();

        $this->display_footer();

        // Delete data from session.
        unset($SESSION->local_registration);
    }

    /**
     * Handles form cancellation.
     *
     * This method redirects the user to the homepage when the form is cancelled.
     *
     * @return void
     */
    private function cancel_form(): void {
        $this->router->redirect(new moodle_url('/'));
    }

    /**
     * Handles form submission.
     *
     * This method processes the submitted form data, stores it in the session,
     * and redirects the user to the review page.
     *
     * @param stdClass $data The submitted form data.
     *
     * @return void
     */
    private function submit_form(stdClass $data): void {
        global $SESSION;
        require_sesskey();

        // Store form data in session.
        $formdata = (array) $data;
        unset($formdata['submitbutton']);
        $SESSION->local_registration = $formdata;

        // Redirect to review page.
        $reviewurl = new moodle_url('/local/registration/index.php?view=review');
        $this->router->redirect($reviewurl);
    }

    /**
     * Display the form.
     *
     * Renders and displays the form using the Moodle Form API.
     *
     * @return void
     */
    private function display_form(): void {
        $this->mform->display();
    }

    /**
     * Retrieves the title of the page.
     *
     * @return string The title of the page.
     */
    protected function get_title(): string {
        return get_string('formtitle', 'local_registration');
    }

    /**
     * Retrieves the description of the page.
     *
     * @return string|null The description of the page, or null if no description is available.
     */
    protected function get_description(): ?string {
        return get_string('formdescription', 'local_registration');
    }
}
