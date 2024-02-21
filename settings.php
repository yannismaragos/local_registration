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
 * Adds settings links to admin tree.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $USER;

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_registration',
        get_string('pluginname', 'local_registration')
    );
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'local_registration/unconfirmedhours',
            get_string('config:unconfirmedhours', 'local_registration'),
            get_string('config:unconfirmedhours_desc', 'local_registration'),
            '24',
            PARAM_INT
        ));

        $settings->add(new admin_setting_configtextarea(
            'local_registration/preapproveddomains',
            new lang_string('config:preapproveddomains', 'local_registration'),
            new lang_string('config:preapproveddomains_desc', 'local_registration'),
            '',
            PARAM_RAW,
            '50',
            '3'
        ));
    }
}
