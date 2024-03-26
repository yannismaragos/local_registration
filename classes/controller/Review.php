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

use moodle_url;
use local_registration\controller\Base;
use local_registration\manager;
use local_registration\helper\Router;
use html_writer;
use single_button;

/**
 * Review controller class.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Review extends Base {
    /**
     * @var Router The Router object.
     */
    protected $router;

    /**
     * Class contructor.
     *
     * @param array $config An associative array of configuration settings. Optional.
     * @param Factory $factory The factory. Optional.
     */
    public function __construct($config = []) {
        parent::__construct($config);

        global $SESSION;
        $this->router = new Router();

        // Check for empty session variable.
        if (empty($SESSION->local_registration)) {
            $this->router->redirect(new moodle_url('/'));
        }

        $this->context = 'system';
        $this->url = new moodle_url('/local/registration/review.php');
        $this->pagelayout = 'standard';
    }

    /**
     * Displays the main content of the page.
     *
     * This method is responsible for rendering the main content of the page.
     *
     * @return void
     */
    protected function display_content(): void {
        global $SESSION, $OUTPUT;

        // Get data from session.
        $data = $SESSION->local_registration;

        // Format data for display.
        $manager = new manager();
        $formatteddata = $manager->format_data($data);

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
            '/local/registration/index.php',
            ['tenantid' => $SESSION->local_registration['tenantid']],
        );
        $submiturl = new moodle_url(
            '/local/registration/index.php',
            [
                'tenantid' => $SESSION->local_registration['tenantid'],
                'task' => 'submit',
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
    }

    /**
     * Retrieves the title of the page.
     *
     * @return string The title of the page.
     */
    protected function get_title(): string {
        return get_string('reviewdatatitle', 'local_registration');
    }

    /**
     * Retrieves the description of the page.
     *
     * @return string|null The description of the page, or null if no description is available.
     */
    protected function get_description(): ?string {
        return '';
    }
}
