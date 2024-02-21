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

/**
 * Registration form rendering file.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_registration\manager;
use local_registration\form\registration;
use local_registration\encryptor;

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');

global $SESSION;

// Set up the page.
$formurl = new moodle_url('/local/registration/form.php');
$PAGE->set_url($formurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('formtitle', 'local_registration'));
$PAGE->set_heading(get_string('formtitle', 'local_registration'));
$PAGE->set_pagelayout('standard');
$manager = new manager();
$encryptor = new encryptor(manager::ENCRYPTION_KEY);

// Get the action from the url.
$action = optional_param('action', '', PARAM_TEXT);

// Instantiate the form.
$mform = new registration();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $mform->get_data()) {
    require_sesskey();

    // Store form data in session.
    $formdata = (array) $data;
    unset($formdata['submitbutton']);
    $SESSION->local_registration = $formdata;

    // Redirect to review page.
    $reviewurl = new moodle_url('/local/registration/review.php');
    redirect($reviewurl);
} else if ($action === 'submit') {
    // Check for empty session variable.
    if (empty($SESSION->local_registration)) {
        redirect(
            $formurl,
            get_string('emptysessiondata', 'local_registration'),
            null,
            'error'
        );
    }

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
        $time = new \DateTime('now');
        $record->timemodified = $time->getTimestamp();
        $record->approved = 0;

        $manager->update_registration_record($record);

        // Notify tenant admins.
        $manager->notify_tenants((int) $record->tenantid, manager::USER_UPDATE);

        // Record updated, display message to user.
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox boxwidthnormal');

        echo html_writer::tag('h3',  get_string('thanks') . ", " . $record->firstname);
        echo html_writer::tag('p',  text_to_html(get_string('registrationupdated', 'local_registration')));
    } else {
        if (!$id = $manager->add_registration_record($SESSION->local_registration)) {
            redirect(
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
        $confirmurl = new moodle_url('/local/registration/confirm.php?id=' . $id . '&hash=' . urlencode($hash));
        $manager->send_confirmation_email($SESSION->local_registration, $confirmurl);
        $unconfirmedhours = get_config('local_registration', 'unconfirmedhours');

        // Confirmation email sent, display message to user.
        $PAGE->set_title(get_string('confirmationemailsent', 'local_registration'));
        $PAGE->set_heading(get_string('confirmationemailsent', 'local_registration'));

        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox boxwidthnormal');

        echo html_writer::tag('h3',  get_string('thanks') . ", " . $SESSION->local_registration['firstname']);
        echo html_writer::tag('p',  text_to_html(get_string('registrationstarted', 'local_registration', $unconfirmedhours)));
    }

    echo $OUTPUT->single_button("$CFG->wwwroot/", get_string('continue'));
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

    // Delete data from session.
    unset($SESSION->local_registration);
} else {
    echo $OUTPUT->header();

    // Display form.
    $mform->display();

    echo $OUTPUT->footer();
}
