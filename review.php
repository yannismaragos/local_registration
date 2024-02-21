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
 * Review registration form field data.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_registration\manager;

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');

// Check for empty session variable.
if (empty($SESSION->local_registration)) {
    redirect(new moodle_url('/'));
}

// Set up the page.
$PAGE->set_url(new moodle_url('/local/registration/review.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('reviewdatatitle', 'local_registration'));
$PAGE->set_heading(get_string('reviewdatatitle', 'local_registration'));
$PAGE->set_pagelayout('standard');

// Get data from session.
$data = $SESSION->local_registration;

// Format data for display.
$manager = new manager();
$formatteddata = $manager->format_data($data);

echo $OUTPUT->header();

// Display form data.
$outputhtml = '';
$outputhtml .= html_writer::start_tag('div', ['class' => '']);

foreach ($formatteddata as $label => $value) {
    $outputhtml .= html_writer::start_tag('div', ['class' => 'form-group row fitem px-3']);
    $outputhtml .= html_writer::start_tag('div', ['class' => 'col-md-3 col-form-label d-flex pb-0 pr-md-0']);
    $outputhtml .= $label;
    $outputhtml .= html_writer::end_tag('div');
    $outputhtml .= html_writer::start_tag('div', ['class' => 'col-md-9 form-inline align-items-start felement py-2']);
    $outputhtml .= $value;
    $outputhtml .= html_writer::end_tag('div');
    $outputhtml .= html_writer::end_tag('div');
}

$outputhtml .= html_writer::end_tag('div');

// Display buttons.
$editurl = new moodle_url(
    '/local/registration/form.php',
    ['tenantid' => $SESSION->local_registration['tenantid']],
);
$submiturl = new moodle_url(
    '/local/registration/form.php',
    [
        'tenantid' => $SESSION->local_registration['tenantid'],
        'action' => 'submit',
    ],
);
$outputhtml .= html_writer::start_tag('div', ['class' => 'form-group row  fitem femptylabel  ']);
$outputhtml .= html_writer::start_tag('div', ['class' => 'col-md-3 col-form-label d-flex pb-0 pr-md-0']);
$outputhtml .= html_writer::end_tag('div');
$outputhtml .= html_writer::start_tag('div', ['class' => 'col-md-9 form-inline align-items-start felement py-2']);
$outputhtml .= $OUTPUT->render(new single_button($editurl, get_string('edit', 'local_registration'), 'get'));
$outputhtml .= $OUTPUT->render(new single_button($submiturl, get_string('submit', 'local_registration'), 'post', single_button::BUTTON_PRIMARY));
$outputhtml .= html_writer::end_tag('div');
$outputhtml .= html_writer::end_tag('div');

echo $outputhtml;

echo $OUTPUT->footer();
