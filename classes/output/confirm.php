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
 * Confirm output file.
 *
 * @package    local_registration
 * @copyright  2024 WIDE Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\output;

use renderer_base;
use local_registration\model\Confirm as ConfirmModel;
use local_registration\helper\Encryptor;
use local_registration\manager;
use tool_tenant\manager as tenantmanager;

/**
 * Confirm output class.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirm implements \renderable, \templatable {
    /**
     * @var ConfirmModel The model.
     */
    public $model = null;

    /**
     * Class constructor.
     *
     * @param ConfirmModel $model The model.
     */
    public function __construct(ConfirmModel $model) {
        $this->model = $model;
    }

    /**
     * Implementation of exporter from templatable interface
     *
     * @param renderer_base $output
     * @return array
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG, $OUTPUT;

        // Check for url params (id, hash).
        $id = required_param('id', PARAM_INT);
        $hash = required_param('hash', PARAM_RAW);

        // Decrypt the hash.
        $encryptor = new Encryptor(manager::ENCRYPTION_KEY);
        $email = $encryptor->decrypt($hash);

        // Get record from 'local_registration'.
        $manager = new manager();
        $record = $manager->get_registration_record($id, $email);

        // Conditions.
        $validhash = $record && $email === $record->email;
        $errorinvalidhash = text_to_html(get_string('errorinvalidhash', 'local_registration'));
        $recordconfirmed = $record && $record->confirmed;
        $emailalreadyconfirmed = text_to_html(get_string('emailalreadyconfirmed', 'local_registration'));
        $recordexpired = $record && $manager->record_has_expired($record->timecreated);
        $confirmrecord = false;
        $erroremailconfirm = text_to_html(get_string('erroremailconfirm', 'local_registration'));
        $istrusteddomain = $record && $manager->is_trusted_domain($record->email);
        $emailconfirmed = text_to_html(get_string('emailconfirmed', 'local_registration'));
        $userid = false;
        $emailconfirmedtrusted = text_to_html(get_string('emailconfirmedtrusted', 'local_registration'));
        $loginbutton = false;

        $data = [
            'validhash' => $validhash,
            'errorinvalidhash' => $errorinvalidhash,
            'recordconfirmed' => $recordconfirmed,
            'emailalreadyconfirmed' => $emailalreadyconfirmed,
            'recordexpired' => $recordexpired,
            'confirmrecord' => $confirmrecord,
            'erroremailconfirm' => $erroremailconfirm,
            'istrusteddomain' => $istrusteddomain,
            'emailconfirmed' => $emailconfirmed,
            'userid' => $userid,
            'emailconfirmedtrusted' => $emailconfirmedtrusted,
            'loginbutton' => $loginbutton,
        ];

        if (!$validhash) {
            return $data;
        }

        if ($recordconfirmed) {
            return $data;
        }

        if ($recordexpired) {
            return $data;
        }

        $confirmrecord = $manager->update_registration_record($record, 'confirmed', '1');
        $data['confirmrecord'] = $confirmrecord;

        if (!$confirmrecord) {
            return $data;
        }

        if ($istrusteddomain) {
            if ($userid = $manager->create_user($record)) {
                $data['userid'] = $userid;
                $manager->update_registration_record($record, 'approved', manager::REGISTRATION_APPROVED);
                $manager->update_registration_record($record, 'assessor', get_admin()->id);
                $tenantmanager = new tenantmanager();
                $tenantmanager->allocate_user($userid, (int) $record->tenantid, 'local_registration', 'new_user');
                $loginbutton = $OUTPUT->single_button("$CFG->wwwroot/login", get_string('login'));
                $data['loginbutton'] = $loginbutton;
            }
        }
        $manager->notify_tenants((int) $record->tenantid, manager::USER_CONFIRMATION);

        return $data;
    }
}
