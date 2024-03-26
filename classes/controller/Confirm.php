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

use moodle_url;
use local_registration\controller\Base;
use local_registration\helper\Router;
use local_registration\encryptor;
use local_registration\manager;
use tool_tenant\manager as tenantmanager;
use html_writer;

/**
 * Confirm controller class.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Confirm extends Base {
    /**
     * @var Router The Router object.
     */
    protected $router;

    /**
     * Class contructor.
     */
    public function __construct() {
        parent::__construct();
        $this->context = 'system';
        $this->url = new moodle_url('/local/registration/confirm.php');
        $this->pagelayout = 'standard';
        $this->router = new Router();
    }

    /**
     * Displays the main content of the page.
     *
     * This method is responsible for rendering the main content of the page.
     *
     * @return void
     */
    protected function display_content(): void {
        global $CFG, $OUTPUT;

        // Check for url params (id, hash).
        $id = required_param('id', PARAM_INT);
        $hash = required_param('hash', PARAM_RAW);

        // Decrypt the hash.
        $encryptor = new encryptor(manager::ENCRYPTION_KEY);
        $email = $encryptor->decrypt($hash);

        // Get record from 'local_registration'.
        $manager = new manager();
        $record = $manager->get_registration_record($id, $email);

        echo $OUTPUT->box_start('generalbox');

        // Check for valid record and valid hash.
        if (!$record || $email !== $record->email) {
            echo html_writer::tag('p', text_to_html(get_string('errorinvalidhash', 'local_registration')));
        } else {
            // Check whether record is already confirmed.
            if ($record->confirmed) {
                echo html_writer::tag('p', text_to_html(get_string('emailalreadyconfirmed', 'local_registration')));
            } else {
                // Check for record expiration.
                if ($manager->record_has_expired($record->timecreated)) {
                    echo html_writer::tag('p', text_to_html(get_string('errorinvalidhash', 'local_registration')));
                } else {
                    // Confirm registration record.
                    if ($manager->update_registration_record($record, 'confirmed', '1')) {
                        if ($manager->is_trusted_domain($record->email)) {
                            // Create user.
                            if ($userid = $manager->create_user($record)) {
                                // Approve registration record.
                                $manager->update_registration_record($record, 'approved', manager::REGISTRATION_APPROVED);

                                // Update assessor id.
                                $manager->update_registration_record($record, 'assessor', get_admin()->id);

                                // Add user to tenant.
                                $tenantmanager = new tenantmanager();
                                $tenantmanager->allocate_user($userid, (int) $record->tenantid, 'local_registration', 'new_user');

                                echo html_writer::tag('p', text_to_html(get_string('emailconfirmedtrusted', 'local_registration')));
                                echo $OUTPUT->single_button("$CFG->wwwroot/login", get_string('login'));
                            }
                        } else {
                            echo html_writer::tag('p', text_to_html(get_string('emailconfirmed', 'local_registration')));
                        }

                        $manager->notify_tenants((int) $record->tenantid, manager::USER_CONFIRMATION);
                    } else {
                        echo html_writer::tag('p', text_to_html(get_string('erroremailconfirm', 'local_registration')));
                    }
                }
            }
        }

        echo $OUTPUT->box_end();
    }

    /**
     * Retrieves the title of the page.
     *
     * @return string The title of the page.
     */
    protected function get_title(): string {
        return get_string('confirmtitle', 'local_registration');
    }

    /**
     * Retrieves the description of the page.
     *
     * @return string|null The description of the page, or null if no description is available.
     */
    protected function get_description(): ?string {
        return '';
    }
}
