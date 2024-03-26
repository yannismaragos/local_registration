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
 * Users output file.
 *
 * @package    local_registration
 * @copyright  2022 WIDE Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\output;

use renderer_base;
use local_datatables\field\selectfield;
use local_datatables\field\datefield;

/**
 * Users output class.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users implements \renderable, \templatable {
    /**
     * Implementation of exporter from templatable interface
     *
     * @param renderer_base $output
     * @return array
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output): array {
        global $USER;

        $data = [
            'userid' => $USER->id,
            'sesskey' => $USER->sesskey,
            'can_export' => true,
        ];

        // Create tenants select field.
        $tenantsfield = new selectfield(
            'tenants',
            ['class' => 'selectpicker form-control', 'data-index' => 4, 'data-live-search' => 'true'],
            'tenants'
        );
        $data['tenantsfield'] = $tenantsfield->render($output);

        // Create countries select field.
        $countriesfield = new selectfield(
            'countries',
            ['class' => 'selectpicker form-control', 'data-index' => 5, 'data-live-search' => 'true'],
            'countries'
        );
        $data['countriesfield'] = $countriesfield->render($output);

        // Create 'gender' select field (profile field).
        $genderfield = new selectfield(
            'gender',
            ['class' => 'selectpicker form-control', 'data-index' => 6, 'data-live-search' => 'true'],
            'profile:gender'
        );
        $data['genderfield'] = $genderfield->render($output);

        // Create 'domain' select field (profile field).
        $domainfield = new selectfield(
            'domain',
            ['class' => 'selectpicker form-control', 'data-index' => 7, 'data-live-search' => 'true'],
            'profile:domain'
        );
        $data['domainfield'] = $domainfield->render($output);

        // Create 'interests' select field (profile field).
        $interestsfield = new selectfield(
            'interests',
            [
                'class' => 'selectpicker form-control',
                'data-index' => 9,
                'data-live-search' => 'true',
                'multiple' => 'multiple',
                'data-actions-box' => 'false',
                'data-selected-text-format' => 'count',
            ],
            'profile:interests'
        );
        $data['interestsfield'] = $interestsfield->render($output);

        // Create boolean select field (Yes, No).
        $confirmedfield = new selectfield(
            'confirmed',
            ['class' => 'selectpicker form-control', 'data-index' => 10],
            'yes_no'
        );
        $data['confirmedfield'] = $confirmedfield->render($output);

        // Create datetime field.
        $timecreatedfield = new datefield(
            'timecreated',
            ['class' => 'form-control datetime', 'data-index' => 11, 'placeholder' => get_string('columntimecreated', 'local_registration')]
        );
        $data['timecreatedfield'] = $timecreatedfield->render($output);

        // Create boolean select field (Yes, No).
        $assessorfield = new selectfield(
            'assessor',
            ['class' => 'selectpicker form-control', 'data-index' => 12],
            'yes_no'
        );
        $data['assessorfield'] = $assessorfield->render($output);

        return $data;
    }
}
