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
 * Notification library.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\lib;

use stdClass;
use core_user;
use moodle_url;
use tool_tenant\manager as tenantmanager;
use core\message\message;
use user_picture;

/**
 * Class Notification.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Notification {
    /**
     * Sends a notification to all administrators of a given tenant,
     * informing them about user verification/update.
     *
     * @param int $tenantid The id of the tenant to notify.
     * @param string $langtype The language type.
     *
     * @return void
     */
    /**
     * Notifies the tenant admins.
     *
     * @param int $tenantid The ID of the tenant.
     * @param string $langtype The language type.
     * @return void
     */
    public function notify_tenant_admins(int $tenantid, string $langtype): void {
        $tenantmanager = new tenantmanager();
        $admins = $tenantmanager->get_tenant_admins($tenantid);
        $users = array_map(function ($id) {
            return core_user::get_user($id);
        }, $admins);

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
     * Sends an email to the user with the provided details.
     *
     * @param array $record An array containing user information.
     * @param moodle_url $url A URL to include in the email.
     *
     * @return bool True if the email is sent successfully, false otherwise.
     */
    public function send_email_to_user(array $record, moodle_url $url): bool {
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $data = new stdClass();
        $data->firstname = $record['firstname'];
        $data->sitename = $site->fullname;
        $data->admin = generate_email_signoff();
        $subject = get_string('emailconfirmationsubject', 'local_registration', format_string($site->fullname));
        $data->link = $url->out(false);
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
     * Sends a popup notification about an unread message to the recipient.
     *
     * This function overrides the Message API and creates a notification directly in the database.
     *
     * @param stdClass $userfrom The sender of the notification.
     * @param stdClass $userto The recipient of the notification.
     *
     * @return int The ID of the inserted notification record.
     */
    public function send_unread_popup_notification(stdClass $userfrom, stdClass $userto): int {
        global $DB;

        $id = $this->send_unread_notification($userfrom, $userto);

        $popup = new stdClass();
        $popup->notificationid = $id;

        $DB->insert_record('message_popup_notifications', $popup);

        return $id;
    }

    /**
     * Inserts a record to table {notifications}.
     *
     * This function should not be used directly. Instead, use the function send_unread_popup_notification().
     *
     * @param stdClass $userfrom The user object representing the sender of the notification.
     * @param stdClass $userto The user object representing the user whose update failed.
     *
     * @return int|false Returns the ID of the inserted notification record on success, or false on failure.
     */
    private function send_unread_notification(stdClass $userfrom, stdClass $userto) {
        global $DB, $CFG, $PAGE;

        $usertourl = "$CFG->wwwroot/user/profile.php?id=$userto->id";
        $usertofullname = "$userto->firstname $userto->lastname";
        $usertopicture = new user_picture($userto);

        $fullmessage = "Hi $userfrom->firstname,\n"
            . "User $usertofullname with ID $userto->id ($usertourl) cannot be updated "
            . "because there is another user with the same email.";
        $fullmessagehtml = "<p>Hi $userfrom->firstname,</p>"
            . "<p>User <b>$usertofullname</b> with ID <b>$userto->id</b> ($usertourl) cannot be updated "
            . "because there is another user with the same email.</p>";
        $smallmessage = "User $usertofullname with ID $userto->id ($usertourl) cannot be updated "
            . "because there is another user with the same email.";

        $record = new stdClass();
        $record->useridfrom = $userto->id;
        $record->useridto = $userfrom->id;
        $record->subject = 'User update failed';
        $record->fullmessage = $fullmessage;
        $record->fullmessageformat = FORMAT_HTML;
        $record->fullmessagehtml = $fullmessagehtml;
        $record->smallmessage = $smallmessage;
        $record->contexturl = $usertourl;
        $record->contexturlname = 'profile';
        $record->component = 'local_username_update';
        $record->eventtype = 'username_update_' . $userto->id;
        $record->timecreated = time();
        $record->customdata = json_encode(
            ['notificationiconurl' => $usertopicture->get_url($PAGE)->out(false)]
        );

        return $DB->insert_record('notifications', $record);
    }

    /**
     * Checks if a notification exists for a specific user, component, and event type.
     *
     * @param stdClass $user The user object for whom the notification is being checked.
     * @param string $component The component of the notification.
     * @param string $eventtype The event type of the notification.
     *
     * @return bool True if the notification exists, false otherwise.
     */
    public function notification_exists(stdClass $user, string $component, string $eventtype): bool {
        global $DB;

        $notificationexists = $DB->record_exists(
            'notifications',
            [
                'useridto' => $user->id,
                'component' => $component,
                'eventtype' => $eventtype,
            ]
        );

        return $notificationexists;
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
}
