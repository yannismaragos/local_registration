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
 * User library.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\lib;

/**
 * Class User.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class User {
    /**
     * Get a user profile field by its shortname.
     *
     * @param string $fieldname The shortname of the profile field to retrieve.
     *
     * @return \stdClass The user profile field as an object, or an empty
     *                   object if not found.
     */
    public function get_profile_field(string $fieldname): \stdClass {
        global $DB;
        $record = $DB->get_record('user_info_field', ['shortname' => $fieldname]);

        return $record ? $record : (object) [];
    }

    /**
     * Retrieve a user's profile field value based on a shortname.
     *
     * This function retrieves the value of a user's profile field with the
     * specified shortname.
     *
     * @param string $shortname The shortname of the profile field.
     *
     * @return string The value of the specified profile field, or an empty
     *                string if not found.
     */
    public function get_profile_field_value(string $shortname): string {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $user = clone ($USER);
        profile_load_data($user);
        $amfieldname = 'profile_field_' . $shortname;

        if (isset($user->$amfieldname)) {
            return (string) $user->$amfieldname;
        }

        return '';
    }

    /**
     * Update a user with the provided data.
     *
     * @param \stdClass $user An object containing user data.
     *
     * @return void
     */
    public function update_user(\stdClass $user): void {
        global $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/user/profile/lib.php');

        // Update user.
        user_update_user($user, false, true);

        // Update user profile fields.
        profile_save_data($user);
    }

    /**
     * Suspend a user.
     *
     * @param \stdClass $user The user object.
     */
    public function suspend_user(\stdClass $user): void {
        $user->suspended = 1;
        user_update_user($user, false, true);
    }

    /**
     * Retrieves the options for a specified user profile field.
     *
     * @param string $shortname The shortname of the user profile field.
     *
     * @return array An associative array containing the options for the
     *               specified user profile field.
     */
    public function get_profile_field_options(string $shortname): array {
        global $DB;

        $data = [];
        $comparedata = $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':shortname');
        $sql = "SELECT * FROM {user_info_field} WHERE $comparedata";
        $params = ['shortname' => $shortname];
        $record = $DB->get_record_sql($sql, $params);

        if ($record) {
            $options = explode(PHP_EOL, $record->param1);

            foreach ($options as $option) {
                $data[$option] = $option;
            }
        }

        return $data;
    }

    /**
     * Create a new user and add custom profile fields.
     *
     * @param object $record An object containing user registration details.
     *
     * @return int The newly created user id.
     */
    public function create_user(object $record): int {
        global $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/user/profile/lib.php');

        // Create user.
        $user = new \stdClass();
        $user->password = 'A12345$a';
        $user->auth = 'manual';
        $user->confirmed = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->email = strtolower($record->email);
        $user->username = strtolower($record->email);
        $user->firstname = $record->firstname;
        $user->lastname = $record->lastname;
        $user->firstnamephonetic = '';
        $user->lastnamephonetic = '';
        $user->middlename = '';
        $user->alternatename = '';
        $time = new \DateTime('now');
        $user->country = $record->country;
        $user->timecreated = $time->getTimestamp();
        $userid = user_create_user($user, true, true);
        $user->id = $userid;

        // Create user profile fields.
        $user->profile_field_gender = $record->gender;
        $user->profile_field_domain = $record->domain;
        $user->profile_field_interests = $record->interests;
        profile_save_data($user);

        // Set user preference for enforcing password change on login.
        set_user_preference('auth_forcepasswordchange', 1, $user->id);

        // Set new password and send email to user.
        setnew_password_and_mail($user);

        return $userid;
    }

    /**
     * Checks if a given email domain is trusted based on pre-approved domains.
     *
     * @param string $email The email address to check.
     *
     * @return bool True if the domain is trusted, false otherwise.
     */
    public function is_trusted_domain(string $email): bool {
        // Get domain from email.
        $emailparts = explode('@', $email);
        $domain = $emailparts[1] ?? null;

        if (empty($domain)) {
            return false;
        }

        // Get trusted domains from plugin settings.
        $config = get_config('local_registration');
        $domains = $config->preapproveddomains;
        $domainsarray = explode("\n", $domains);
        $domainsarray = array_map(function ($domain) {
            return trim($domain);
        }, $domainsarray);

        return in_array($domain, $domainsarray);
    }
}
