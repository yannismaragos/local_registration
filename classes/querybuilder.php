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
 * querybuilder class.
 *
 * The querybuilder class is responsible for constructing SQL queries
 * based on DataTables request data. It provides a method to build
 * a query that can be used to fetch data from the database.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration;

use local_datatables\ssp\dtquerybuilder;

/**
 * querybuilder class.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class querybuilder extends dtquerybuilder {
    /**
     * Build a SELECT query for fetching lr and tenantname data
     */
    public function build_select($requestdata, $tableid) {
        // Define the columns to be selected.
        $query = "SELECT lr.*, t.name as tenantname";

        // Return the query.
        return $query;
    }

    public function build_sql($requestdata, $tableid) {
        $tablealias = 'lr';

        // Build columns options JOIN.
        $columns = [
            'confirmed' => [
                '0' => get_string('no'),
                '1' => get_string('yes'),
            ],
            'country' => get_string_manager()->get_list_of_countries(true),
            // 'test' => [
            //     'sql:' => "(CASE WHEN lr.assessor IS NOT NULL THEN 1 ELSE 0 END)",
            //     '0' => get_string('no'),
            //     '1' => get_string('yes'),
            // ]
        ];

        $columnsjoins = $this->map_options_to_join($tablealias, $columns);

        $tenantjoin = "LEFT JOIN {tool_tenant} t ON t.id = $tablealias.tenantid";

        $query = "FROM {local_registration} $tablealias $columnsjoins $tenantjoin
            WHERE $tablealias.confirmed IN (0, 1) AND $tablealias.approved IN (0, -2)";

        return $query;
    }
}
