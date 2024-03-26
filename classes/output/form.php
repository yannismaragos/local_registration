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
 * Form output file.
 *
 * @package    local_registration
 * @copyright  2022 WIDE Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\output;

use renderer_base;
use local_registration\model\Users as UsersModel;

/**
 * Form output class.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form implements \renderable, \templatable {
    /**
     * @var UsersModel The model.
     */
    public $model = null;

    /**
     * Class constructor.
     *
     * @param UsersModel $model The model.
     */
    public function __construct(UsersModel $model) {
        $this->model = $model;
    }

    /**
     * Implementation of exporter from templatable interface
     *
     * @param renderer_base $output
     * @return array
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output): array {
        $data = [];

        return $data;
    }
}
