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
 * Users table renderer file.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class local_registration_renderer.
 *
 * @package    local_registration
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_registration_renderer extends plugin_renderer_base {
    /**
     * Renders the users page.
     *
     * @param \local_registration\output\users $outputpage
     * @return string HTML
     * @throws moodle_exception
     */
    protected function render_users(\local_registration\output\users $outputpage) {
        $data = $outputpage->export_for_template($this);

        return $this->render_from_template('local_registration/users', $data);
    }

    /**
     * Renders the form page.
     *
     * @param \local_registration\output\form $outputpage
     * @return string HTML
     * @throws moodle_exception
     */
    protected function render_form(\local_registration\output\form $outputpage) {
        $data = $outputpage->export_for_template($this);

        return $this->render_from_template('local_registration/form', $data);
    }

    /**
     * Renders the review page.
     *
     * @param \local_registration\output\review $outputpage
     * @return string HTML
     * @throws moodle_exception
     */
    protected function render_review(\local_registration\output\review $outputpage) {
        $data = $outputpage->export_for_template($this);

        return $this->render_from_template('local_registration/review', $data);
    }

    /**
     * Renders the confirm page.
     *
     * @param \local_registration\output\confirm $outputpage
     * @return string HTML
     * @throws moodle_exception
     */
    protected function render_confirm(\local_registration\output\confirm $outputpage) {
        $data = $outputpage->export_for_template($this);

        return $this->render_from_template('local_registration/confirm', $data);
    }
}
