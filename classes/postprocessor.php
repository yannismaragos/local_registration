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
 * postprocessor class.
 *
 * The postprocessor class is responsible for post-processing the items
 * fetched from the database. It provides a method to apply additional
 * processing logic to the retrieved data.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration;

use local_datatables\ssp\dtpostprocessor;
use local_registration\manager;

/**
 * postprocessor class.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class postprocessor extends dtpostprocessor {
    /**
     * Process the fetched items after pagination.
     *
     * @param array $items The items to be processed.
     *
     * @return array The processed items.
     */
    public function process_items($items): array {
        global $DB;

        $processeditems = [];
        $allcountries = get_string_manager()->get_list_of_countries(true);

        foreach ($items as $item) {
            $item->country_formatted = isset($allcountries[$item->country]) ? $allcountries[$item->country] : '';
            $item->interests_formatted = !empty(json_decode($item->interests)) ? implode('<br>', json_decode($item->interests)) : '';
            $item->notified = $item->approved == manager::REGISTRATION_NOTIFIED ? 1 : 0;
            $item->duplicateid = 0;
            $item->confirmed_formatted = $item->confirmed == 0 ? get_string('no') : get_string('yes');

            // Use DD/MM/YY date format (= 'strftimedatefullshort').
            $item->timecreated_formatted = userdate($item->timecreated, get_string('strftimedatefullshort'));

            // Check for possible duplicate users.
            // Fetch all user records with names that start with $item->firstname and $item->lastname.
            $users = $DB->get_records_select(
                'user',
                "firstname LIKE ? AND lastname LIKE ? AND deleted = 0",
                ["$item->firstname%", "$item->lastname%"]
            );

            if (!empty($users)) {
                $users = array_values($users);
                $item->duplicateid = $users[0]->id;
            }

            $processeditems[] = $item;
        }

        return $items;
    }
}
