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
 * This file contains the form for handling the 'notify' action for the users table.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\form;

use moodle_url;
use core_form\dynamic_form;
use local_registration\Factory;
use local_registration\model\Form as FormModel;
use local_registration\lib\Notification as NotificationLib;
use local_registration\helper\Encryptor;
use DateTime;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * The form for handling the 'notify' action for the users table.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notify_form extends dynamic_form {

    /**
     * Returns context where this form is used.
     *
     * @return \context
     */
    public function get_context_for_dynamic_submission(): \context {
        return \context_system::instance();
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or
     * submitted via AJAX.
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $params = [];

        return new moodle_url('/local/registration', $params);
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception.
     *
     * Sometimes permission check may depend on the action and/or id of the entity.
     * If necessary, form data is available in $this->_ajaxformdata or
     * by calling $this->optional_param().
     */
    public function check_access_for_dynamic_submission(): void {
    }

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition(): void {
        // Instantiate the form.
        $mform = $this->_form;

        $id = $this->optional_param('id', null, PARAM_INT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $this->set_data(['id', $id]);

        // Reason text.
        $mform->addElement(
            'textarea',
            'reason',
            get_string('modal:notifyreason', 'local_registration'),
            'wrap="virtual" rows="4" cols="100",maxlength=500, class="required"'
        );
        $mform->setType('reason', PARAM_NOTAGS);
        $mform->addRule('reason', get_string('maxlength500', 'local_registration'), 'maxlength', 500, 'server');
        $mform->addRule('reason', get_string('modal:reasonempty', 'local_registration'), 'required', null, 'server');
        $mform->addHelpButton('reason', 'modal:notifyreason', 'local_registration');
    }

    /**
     * Load in existing data as form defaults.
     *
     * Can be overridden to retrieve existing values from db by entity id and also
     * to preprocess editor and filemanager elements.
     */
    public function set_data_for_dynamic_submission(): void {
    }

    /**
     * Process the form submission, used if form was submitted via AJAX.
     *
     * This method can return scalar values or arrays that can be json-encoded,
     * they will be passed to the caller JS.
     *
     * @return void
     */
    public function process_dynamic_submission(): void {
        global $USER;

        $data = $this->get_data();

        if ($data) {
            $id = (int) $data->id;
            $reason = $data->reason;

            // Get registration record.
            $factory = new Factory('local_registration');
            $formmodel = $factory->create_model('Form');
            $notificationlib = new NotificationLib();
            $record = $formmodel->get_registration_record($id);

            // Notify registration record.
            $formmodel->update_registration_record($record, 'approved', FormModel::REGISTRATION_NOTIFIED);

            // Update assessor id.
            $formmodel->update_registration_record($record, 'assessor', $USER->id);

            // Update timemodified.
            $time = new DateTime('now');
            $formmodel->update_registration_record($record, 'timemodified', $time->getTimestamp());

            // Construct url for editing registration record.
            $encryptor = new Encryptor(Encryptor::ENCRYPTION_KEY);
            $hash = $encryptor->encrypt($record->email);
            $url = new moodle_url('/local/registration/form.php?id=' . $id . '&hash=' . urlencode($hash));

            // Send email notification to user.
            $notificationlib->send_email_for_edit($record, $reason, $url);
        }
    }
}
