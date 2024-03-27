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

namespace local_registration\controller;

use local_registration\controller\Base;
use tool_tenant\tenancy;
use core\notification;
use moodle_url;

/**
 * Users controller class.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Users extends Base {
    /**
     * Class constructor.
     *
     * @param array $config An associative array of configuration settings. Optional.
     * @param Factory $factory The factory. Optional.
     */
    public function __construct($config = []) {
        parent::__construct($config);
        $this->context = 'system';
        $this->url = new moodle_url('/local/registration/index.php?view=users');
        $this->pagelayout = 'standard';
    }

    /**
     * Retrieves the title of the page.
     *
     * @return string The title of the page.
     */
    protected function get_title(): string {
        return get_string('userstitle', 'local_registration');
    }

    /**
     * Display method for rendering the header of the page.
     *
     * This method sets up the page URL, context, title, heading, and page layout.
     * It also displays the header output and, if available, a description box.
     *
     * @return void
     */
    protected function display_header(): void {
        global $PAGE;
        $PAGE->requires->css(new moodle_url('/local/registration/style/custom.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/custom.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/bootstrap-select/bootstrap-select.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/buttons.bootstrap4.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/buttons.dataTables.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/dataTables.bootstrap4.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/dataTables.dateTime.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/jquery.dataTables.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/responsive.bootstrap4.min.css'));
        $PAGE->requires->css(new moodle_url('/local/datatables/style/datatables/responsive.dataTables.min.css'));

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

        parent::display_header();
    }

    /**
     * Renders the main content of the page.
     *
     * @return void
     */
    public function display_content(): void {
        global $USER;
        require_login();

        // Access control.
        $istenantadmin = \tool_tenant\manager::is_tenant_admin(tenancy::get_tenant_id(), $USER->id);

        if (!is_siteadmin() && !$istenantadmin) {
            notification::error(get_string('errorcapability', 'local_registration'));
        }

        parent::display_content();
    }
}
