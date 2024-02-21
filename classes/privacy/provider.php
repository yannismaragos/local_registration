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
 * Privacy Subsystem implementation for local_registration.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\privacy;

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for local_registration.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin stores user data.
    \core_privacy\local\metadata\provider,
    // This plugin may provide access to and deletion of user data.
    \core_privacy\local\request\plugin\provider {
    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'local_registration',
            [
                'firstname' => 'privacy:metadata:local_registration:firstname',
                'lastname' => 'privacy:metadata:local_registration:lastname',
                'email' => 'privacy:metadata:local_registration:email',
            ],
            'privacy:metadata:local_registration:users'
        );

        return $items;
    }

    /**
     * Return all contexts for this userid.
     *
     * @param  int $userid The user ID.
     * @return contextlist The list of context IDs.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();

        $user = \core_user::get_user($userid);
        $email = $user->email;
        $comparedata = $DB->sql_compare_text('email') . " = " . $DB->sql_compare_text(':email');

        $sql = "SELECT COUNT(1)
                FROM {local_registration}
                WHERE $comparedata";

        $params = ['email' => $email];

        if ($DB->count_records_sql($sql, $params)) {
            // We use system context.
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param  approved_contextlist $contextlist The list of approved contexts for a user.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // We're only interested in the system context.
        $contexts = array_filter($contextlist->get_contexts(), function ($context) {
            return $context instanceof context_system;
        });

        if (empty($contexts)) {
            return;
        }

        $context = reset($contexts);
        $contextpath = [get_string('pluginname', 'local_registration')];
        $email = $contextlist->get_user()->email;
        $comparedata = $DB->sql_compare_text('email') . " = " . $DB->sql_compare_text(':email');

        $sql = "SELECT firstname, lastname, email
                FROM {local_registration}
                WHERE $comparedata";

        $params = ['email' => $email];

        $data = [];

        $recordset = $DB->get_recordset_sql($sql, $params);
        foreach ($recordset as $record) {
            $data[] = [
                'firstname' => format_string($record->firstname),
                'lastname' => format_string($record->lastname),
                'email' => format_string($record->email),
            ];
        }
        $recordset->close();

        if (count($data) > 0) {
            writer::with_context($context)->export_data($contextpath, (object)$data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $DB->delete_records('local_registration');
    }

    /**
     * Delete all user data for approved contexts lists provided in the collection.
     *
     * This call relates to the forgetting of an entire user.
     *
     * Note: userid and component are stored in each respective approved_contextlist.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $email = $contextlist->get_user()->email;
        $comparedata = $DB->sql_compare_text('email') . " = " . $DB->sql_compare_text(':email');
        $params = ['email' => $email];

        $DB->delete_records_select(
            'local_registration',
            "$comparedata",
            $params
        );
    }
}
