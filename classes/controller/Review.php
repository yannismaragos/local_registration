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
use local_registration\helper\Router;

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
        $this->url = new moodle_url('/local/registration/index.php?view=review');
        $this->pagelayout = 'standard';
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
