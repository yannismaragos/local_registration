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
 * Confirm user registration.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_registration\manager;
use local_registration\encryptor;
use tool_tenant\manager as tenantmanager;

// phpcs:ignore moodle.Files.RequireLogin.Missing
require(__DIR__ . '/../../config.php');

// Check for url params (id, hash).
$id = required_param('id', PARAM_INT);
$hash = required_param('hash', PARAM_RAW);

// Set up the page.
$PAGE->set_url(new moodle_url('/local/registration/confirm.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('confirmtitle', 'local_registration'));
$PAGE->set_heading(get_string('confirmtitle', 'local_registration'));
$PAGE->set_pagelayout('standard');

// Decrypt the hash.
$encryptor = new encryptor(manager::ENCRYPTION_KEY);
$email = $encryptor->decrypt($hash);

// Get record from 'local_registration'.
$manager = new manager();
$record = $manager->get_registration_record($id, $email);

echo $OUTPUT->header();
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
echo $OUTPUT->footer();
