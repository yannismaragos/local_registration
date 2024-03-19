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
 * Users table.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_tenant\manager;
use tool_tenant\tenancy;
use core\notification;
use local_registration\output\users;

require_once(__DIR__ . '/../../config.php');
require_login();

$PAGE->set_url(new moodle_url('/local/registration/users.php'));
$PAGE->set_context(\core\context\system::instance());

// Access control.
$istenantadmin = manager::is_tenant_admin(tenancy::get_tenant_id(), $USER->id);

if (
    !is_siteadmin() && !$istenantadmin
) {
    $output = $PAGE->get_renderer('local_registration');
    echo $output->header();
    notification::error(get_string('errorcapability', 'local_registration'));
    echo $output->footer();
    exit;
}

$PAGE->requires->strings_for_js(
    [
        'duplicate',
        'notified',
        'approve',
        'reject',
        'rejectreason',
        'notify',
        'notifyreason',
    ],
    'local_registration'
);

$PAGE->requires->strings_for_js(
    [
        'no',
        'yes',
    ],
    'core'
);

$PAGE->set_title(get_string('userstitle', 'local_registration'));
$PAGE->set_heading(get_string('pluginname', 'local_registration'));

$PAGE->requires->css(new moodle_url('/local/registration/style/custom.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/custom.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/bootstrap-select/bootstrap-select.min.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/buttons.bootstrap4.min.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/buttons.dataTables.min.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/dataTables.bootstrap4.min.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/jquery.dataTables.min.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/responsive.bootstrap4.min.css'));
$PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/responsive.dataTables.min.css'));

$output = $PAGE->get_renderer('local_registration');

echo $output->header();

// Display table.
$outputpage = new users();
echo $output->render($outputpage);

echo $output->footer();
