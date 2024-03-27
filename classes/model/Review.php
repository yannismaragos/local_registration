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
use tool_tenant\tenancy;

/**
 * Review model class.
 *
 * @package    local_registration
 * @author     Yannis Maragos <maragos.y@wideservices.gr>
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Review extends BaseModel {
    /**
     * Formats and reindexes the given data array.
     *
     * It includes the retrieval of tenant information, converts interests
     * into a comma-separated string, converts country codes to names,
     * reindexes the array, and removes the 'policies' field.
     *
     * @param array $data The input data array to be formatted.
     *
     * @return array The formatted data array.
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

        $formatted = array_map(function ($key, $value) {
            return ['key' => $key, 'value' => $value];
        }, array_keys($data), $data);

        return $formatted;
    }
}
