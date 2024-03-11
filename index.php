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
 * Index file for the application.
 *
 * This file serves as the entry point for the application.
 * It includes the necessary configuration file and handles the routing logic.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');

// Get the view and task from the url.
$view = optional_param('view', 'form', PARAM_ALPHA);
$task = optional_param('task', 'display', PARAM_ALPHAEXT);

// Loads the required controller class.
$controller = 'local_registration\\controller\\' . ucfirst($view);

if (!class_exists($controller)) {
    throw new moodle_exception('viewerror', 'local_registration', ucfirst($view));
}

$instance = new $controller();

if (!method_exists($instance, $task)) {
    throw new moodle_exception('taskerror', 'local_registration', ucfirst($task));
}

$instance->$task();
