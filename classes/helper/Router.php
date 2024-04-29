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
 * Router helper.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\helper;

use moodle_url;

/**
 * Class Router.
 *
 * Handles routing and redirection within the application.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Router {
    /**
     * Redirects the user to a specified URL with optional message and delay.
     *
     * This function handles redirection by either using JavaScript or PHP redirect headers,
     * depending on whether headers have been sent already.
     *
     * @param moodle_url $url          The URL to redirect to.
     * @param string     $message      Optional. The message to display after redirection.
     * @param int        $delay        Optional. The delay in seconds before redirection.
     * @param string     $messagetype  Optional. The type of message (e.g., NOTIFY_INFO, NOTIFY_ERROR).
     *                                 Default is \core\output\notification::NOTIFY_INFO.
     * @return void
     */
    public function redirect(
        moodle_url $url,
        string $message = '',
        int $delay = null,
        string $messagetype = \core\output\notification::NOTIFY_INFO
    ): void {
        if (headers_sent()) {
            global $PAGE, $SESSION;
            $PAGE->set_context(null);
            $PAGE->set_pagelayout('redirect');
            $PAGE->set_title(get_string('pageshouldredirect', 'moodle'));

            if (!empty($message)) {
                // Add the message to the session notification stack.
                $message = clean_text($message);
                $SESSION->notifications[] = (object) [
                    'message' => $message,
                    'type' => $messagetype,
                ];
            }

            // Make sure the session is closed properly, this prevents problems in IIS
            // and also some potential PHP shutdown issues.
            \core\session\manager::write_close();

            // Redirect via JavaScript.
            $url = $url->out(false);
            echo '<script>
            document.querySelector("body").style.display = "none";
            document.location.href=' . json_encode($url) . ";
            </script>\n";
        } else {
            redirect($url, $message, $delay, $messagetype);
        }

        exit;
    }
}
