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
 * Policies library.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\lib;

use tool_policy\api as policy_api;
use moodle_url;

/**
 * Class Policies.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Policies {
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
}
