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
 * Tenant library.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\lib;

use tool_tenant\tenancy;

/**
 * Class Tenant.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Tenant {
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
     * Gets the name of the tenant.
     *
     * @param int $id The ID of the tenant to get the name for.
     *
     * @return string The name of the tenant.
     */
    public function get_tenant_name(int $id): string {
        return tenancy::get_tenant_name_from_id($id);
    }
}
