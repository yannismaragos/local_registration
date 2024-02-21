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
 * Scheduled task class for expiration control of registration records.
 *
 * @package    local_registration
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\task;

use core\task\scheduled_task;
use local_registration\manager;

/**
 * Scheduled task class for expiration control of registration records.
 *
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class expiration_control extends scheduled_task {
    /**
     * Return the task name as shown in admin screens.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('expirationcontroltask', 'local_registration');
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute(): void {
        $manager = new manager();
        $manager->delete_expired_records();
    }
}
