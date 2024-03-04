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
 * Manager class.
 *
 * Central coordination point for the plugin. Manages the lifecycle of the
 * plugin, coordinates interactions between different components, and handles
 * configuration and global settings.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration;

use moodle_url;
use stdClass;
use core_user;
use tool_tenant\tenancy;
use tool_policy\api as policy_api;
use tool_tenant\manager as tenantmanager;
use core\message\message;

/**
 * Manager class.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
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
     * Encryption key.
     *
     * @var string
     */
    public const ENCRYPTION_KEY = "8r26kHwddGS13f*jfFRT6dfiglKd9UsG";

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
     * Manager class constructor.
     */
    public function __construct() {
    }

    /**
     * Checks if a tenant ID exists in the database.
     *
     * @param int $id The ID of the tenant to check.
     *
     * @return bool True if the tenant exists, false otherwise.
     */
    public function tenant_exists(int $id): bool {
        global $DB;

        return $DB->record_exists('tool_tenant', ['id' => $id]);
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
            $time = new \DateTime('now');
            $data['timecreated'] = $time->getTimestamp();

            return $DB->insert_record('local_registration', $data);
        }

        return false;
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
     * Retrieves a list of policies with associated information.
     *
     * This function fetches raw policy data from the policy API, processes it,
     * and returns an array containing policy details such as ID, name, and URL.
     *
     * @return array An associative array where keys are policy IDs, and values
     *               are arrays containing 'name' and 'url.
     */
    public function get_policies(): array {
        $policiesraw = policy_api::list_policies();
        $policies = [];

        foreach ($policiesraw as $policy) {
            if (empty($policy->currentversion)) {
                continue;
            }

            $id = $policy->currentversion->policyid;
            $policies[$id] = [];
            $policies[$id]['name'] = $policy->currentversion->name;
            $url = new moodle_url("/admin/tool/policy/view.php?policyid=$id");
            $policies[$id]['url'] = $url->out();
        }

        return $policies;
    }

    /**
     * Formats a list of policies into a string with hyperlinks.
     *
     * This function takes an array of policies and generates a formatted
     * string with hyperlinks to each policy's URL and name.
     *
     * @param array $policies An associative array where keys are policy IDs,
     *                        and values are arrays containing 'name' and 'url'
     *                        information for each policy.
     *
     * @return string A formatted string with hyperlinks to each policy.
     */
    public function format_policies(array $policies): string {
        $policieslinks = array_map(function ($policy) {
            return '<a href="' . $policy['url'] . '" target="_blank">' . $policy['name'] . '</a>';
        }, $policies);

        return implode(', ', $policieslinks);
    }

    /**
     * Formats and reindexes the given data array.
     *
     * It includes the retrieval of tenant information, converts interests
     * into a comma-separated string, converts country codes to names,
     * reindexes the array with predefined labels, and removes the 'policies'
     * field.
     *
     * @param array $data The input data array to be formatted.
     *
     * @return array The formatted data array with keys reindexed according to
     *               predefined labels.
     */
    public function format_data(array $data): array {

        // Format fields.
        $tenantname = tenancy::get_tenant_name_from_id($data['tenantid']);
        $data['tenantid'] = $tenantname;
        $data['interests'] = implode(', ', $data['interests']);
        $allcountries = get_string_manager()->get_list_of_countries(true);
        $data['country'] = $allcountries[$data['country']];

        $labels = [
            'id',
            'hash',
            'Tenant',
            'First name',
            'Last name',
            'Email',
            'Country',
            'Gender',
            'Position',
            'Domain',
            'Comments',
            'Fields of interest',
            'policies',
        ];

        // Reindex $data with $labels.
        $data = array_combine($labels, $data);

        unset($data['id']);
        unset($data['hash']);
        unset($data['policies']);

        return $data;
    }

    /**
     * Sends a confirmation email to the user with the provided details.
     *
     * @param array $record An array containing user information.
     * @param moodle_url $confirmationurl The URL for confirming the user's email.
     *
     * @return bool True if the email is sent successfully, false otherwise.
     */
    public function send_confirmation_email(array $record, moodle_url $confirmationurl): bool {
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $data = new stdClass();
        $data->firstname = $record['firstname'];
        $data->sitename = $site->fullname;
        $data->admin = generate_email_signoff();
        $subject = get_string('emailconfirmationsubject', 'local_registration', format_string($site->fullname));
        $data->link = $confirmationurl->out(false);
        $data->unconfirmedhours = get_config('local_registration', 'unconfirmedhours');
        $message = get_string('emailconfirmation', 'local_registration', $data);
        $messagehtml = text_to_html(get_string('emailconfirmation', 'local_registration', $data), false, false, true);

        // Create a dummy user object.
        $user = new stdClass();
        $user->id = 999999;
        $user->email = strtolower($record['email']);
        $user->username = strtolower($record['email']);
        $user->firstname = $record['firstname'];
        $user->lastname = $record['lastname'];
        $user->firstnamephonetic = '';
        $user->lastnamephonetic = '';
        $user->middlename = '';
        $user->alternatename = '';

        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
    }

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
        $user = new stdClass();
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
     * Sends a notification to all administrators of a given tenant,
     * informing them about user verification/update.
     *
     * @param int $tenantid The id of the tenant to notify.
     * @param int $type The type of notification.
     *
     * @return void
     */
    public function notify_tenants(int $tenantid, int $type): void {
        $tenantmanager = new tenantmanager();
        $admins = $tenantmanager->get_tenant_admins($tenantid);
        $users = array_map(function ($id) {
            return core_user::get_user($id);
        }, $admins);

        $langtype = $type == self::USER_CONFIRMATION ? 'confirm' : 'update';

        foreach ($users as $user) {
            $message = new message();
            $message->component = 'local_registration';
            $message->name = 'notifytenants';
            $message->userfrom = core_user::get_noreply_user();
            $message->userto = $user;
            $message->subject = get_string('notifytenant:subject' . $langtype, 'local_registration');
            $message->fullmessage = get_string('notifytenant:body' . $langtype, 'local_registration');
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = get_string('notifytenant:body' . $langtype, 'local_registration');
            $message->smallmessage = get_string('notifytenant:subject' . $langtype, 'local_registration');
            $message->notification = 1;
            $message->contexturl = new moodle_url('/local/registration/users.php');
            $message->contexturlname = get_string('notifytenant:contexturlname', 'local_registration');

            // Send the message.
            message_send($message);
        }
    }

    /**
     * Sends an email with the provided details.
     *
     * @param object $record An array containing user information.
     * @param string $reason Text entered in a form.
     *
     * @return bool True if the email is sent successfully, false otherwise.
     */
    public function send_email_for_rejection(object $record, string $reason): bool {
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $data = new stdClass();
        $data->firstname = $record->firstname;
        $data->sitename = $site->fullname;
        $data->admin = generate_email_signoff();
        $subject = get_string('emailrejectsubject', 'local_registration', format_string($site->fullname));
        $data->reason = $reason;
        $message = get_string('emailrejectbody', 'local_registration', $data);
        $messagehtml = text_to_html(get_string('emailrejectbody', 'local_registration', $data), false, false, true);

        // Create a dummy user object.
        $user = new stdClass();
        $user->id = 999999;
        $user->email = strtolower($record->email);
        $user->username = strtolower($record->email);
        $user->firstname = $record->firstname;
        $user->lastname = $record->lastname;
        $user->firstnamephonetic = '';
        $user->lastnamephonetic = '';
        $user->middlename = '';
        $user->alternatename = '';

        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
    }

    /**
     * Sends an email with the provided details.
     *
     * @param object      $record  An object containing user information.
     * @param string      $reason  Text entered in a form.
     * @param moodle_url  $url  The URL for confirming the user's email.
     *
     * @return bool True if the email is sent successfully, false otherwise.
     */
    public function send_email_for_edit(object $record, string $reason, moodle_url $url): bool {
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $data = new stdClass();
        $data->firstname = $record->firstname;
        $data->sitename = $site->fullname;
        $data->admin = generate_email_signoff();
        $subject = get_string('emailnotifysubject', 'local_registration', format_string($site->fullname));
        $data->link = $url->out(false);
        $data->reason = $reason;
        $message = get_string('emailnotifybody', 'local_registration', $data);
        $messagehtml = text_to_html(get_string('emailnotifybody', 'local_registration', $data), false, false, true);

        // Create a dummy user object.
        $user = new stdClass();
        $user->id = 999999;
        $user->email = strtolower($record->email);
        $user->username = strtolower($record->email);
        $user->firstname = $record->firstname;
        $user->lastname = $record->lastname;
        $user->firstnamephonetic = '';
        $user->lastnamephonetic = '';
        $user->middlename = '';
        $user->alternatename = '';

        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
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
