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

namespace local_registration\model;

use local_registration\model\Base as BaseModel;
use core_user;
use DateTime;

/**
 * Form model class.
 *
 * @package    local_registration
 * @author     Yannis Maragos <maragos.y@wideservices.gr>
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Form extends BaseModel {
    /**
     * Value for pending registration record.
     *
     * @var int
     */
    public const REGISTRATION_PENDING = 0;

    /**
     * Value for approved registration record.
     *
     * @var int
     */
    public const REGISTRATION_APPROVED = 1;

    /**
     * Value for rejected registration record.
     *
     * @var int
     */
    public const REGISTRATION_REJECTED = -1;

    /**
     * Value for notified registration record.
     *
     * @var int
     */
    public const REGISTRATION_NOTIFIED = -2;

    /**
     * Value for user confirmation notification type.
     *
     * @var int
     */
    public const USER_CONFIRMATION = 0;

    /**
     * Value for user update notification type.
     *
     * @var int
     */
    public const USER_UPDATE = 1;

    /**
     * Updates the registration record for a user.
     *
     * @param object $record The registration record object to be updated.
     * @param string|null $column (Optional) The name of the column to be updated.
     * @param string|null $value (Optional) The new value for the specified column.
     *
     * @return bool True if the update is successful, false otherwise.
     */
    public function update_registration_record(object $record, ?string $column = null, ?string $value = null): bool {
        global $DB;

        if (!empty($value) && is_numeric($value)) {
            $value = (int) $value;
        }

        if (!empty($column) && !empty($value)) {
            $record->$column = $value;
        }

        return $DB->update_record('local_registration', $record);
    }

    /**
     * Inserts a new registration record into the 'local_registration' table
     * only if there is no existing record with the same email and no record
     * in the 'user' table.
     *
     * @param array $data The data to be inserted into the record.
     *
     * @return mixed Returns the id of the inserted record if successful
     *               or false if a record with the same email already exists.
     */
    public function add_registration_record(array $data) {
        global $DB;

        if (
            !$this->get_registration_record(null, $data['email']) &&
            !core_user::get_user_by_email($data['email'])
        ) {
            $data['interests'] = json_encode($data['interests']);
            $time = new DateTime('now');
            $data['timecreated'] = $time->getTimestamp();

            return $DB->insert_record('local_registration', $data);
        }

        return false;
    }

    /**
     * Retrieves the registration record associated with a given ID and email
     * address from the 'local_registration' table.
     *
     * @param int|null    $id    (Optional) The record ID.
     * @param string|null $email (Optional) The record email address.
     *
     * @return mixed Returns the registration record object if it exists, or false if not found.
     */
    public function get_registration_record(?int $id = null, ?string $email = null) {
        global $DB;

        $comparedata = $DB->sql_compare_text('email') . " = " . $DB->sql_compare_text(':email');

        if (!empty($id) && !empty($email)) {
            $where = "id = :id AND $comparedata";
            $params = ['id' => $id, 'email' => $email];
        } else if (!empty($id) && empty($email)) {
            $where = "id = :id";
            $params = ['id' => $id];
        } else if (empty($id) && !empty($email)) {
            $params = ['email' => $email];
            $where = $comparedata;
        }

        $sql = "SELECT * FROM {local_registration} WHERE $where";

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Deletes expired records from the 'local_registration' table based on
     * a specified time threshold.
     *
     * @return void
     */
    public function delete_expired_records() {
        global $DB;

        // Get the number of hours threshold from plugin settings.
        $unconfirmedhours = get_config('local_registration', 'unconfirmedhours');

        // Calculate the timestamp X hours ago.
        $timestampthreshold = time() - $unconfirmedhours * 3600;

        // SQL query to select records older than X hours.
        $sql = "SELECT id FROM {local_registration} WHERE timecreated < :timestampthreshold AND confirmed = 0";
        $params = ['timestampthreshold' => $timestampthreshold];
        $ids = $DB->get_fieldset_sql($sql, $params);

        // Delete the selected ids.
        if (!empty($ids)) {
            $DB->delete_records_list('local_registration', 'id', $ids);

            // Log the result.
            mtrace(get_string('expirationcontroltaskdeleterecords', 'local_registration'));
            foreach ($ids as $id) {
                mtrace($id);
            }
        }
    }

    /**
     * Check if a record has expired based on a timestamp and a configured
     * expiration threshold.
     *
     * @param int $timestamp The timestamp to check against for expiration.
     *
     * @return bool True if the record has expired, false otherwise.
     */
    public function record_has_expired($timestamp): bool {
        // Get the number of hours threshold from plugin settings.
        $expirationhours = get_config('local_registration', 'unconfirmedhours');

        // Get the current timestamp.
        $currenttimestamp = time();

        // Calculate the timestamp difference in seconds.
        $timestampdifference = $currenttimestamp - $timestamp;

        // Convert the expiration hours to seconds.
        $expirationseconds = $expirationhours * 3600;

        // Check if the record has expired.
        if ($timestampdifference > $expirationseconds) {
            return true;
        } else {
            return false;
        }
    }
}
