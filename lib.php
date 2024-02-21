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
 * Plugin callbacks.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define menu items to be added to the Workplace launcher.
 *
 * @return array[]
 */
function local_registration_theme_workplace_menu_items(): array {
    global $OUTPUT, $USER;

    $menuitems = [];
    $tenantid = \tool_tenant\tenancy::get_tenant_id();
    $isglobal = is_siteadmin() ? true : false;

    if (is_siteadmin() || \tool_tenant\manager::is_tenant_admin($tenantid, $USER->id)) {
        $menuitems[] = [
            'url' => new moodle_url("/local/registration/index.php"),
            'name' => get_string('workplacemenuitemtitle', 'local_registration'),
            'imageurl' => $OUTPUT->image_url('usermanagement', 'tool_tenant')->out(false),
            'isglobal' => $isglobal,
        ];
    }

    return $menuitems;
}
