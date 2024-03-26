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
use local_registration\Factory;
use local_registration\model\Base as BaseModel;

/**
 * Base controller class.
 *
 * @package    local_registration
 * @author     Yannis Maragos <maragos.y@wideservices.gr>
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class Base {
    /**
     * @var string The name of the controller.
     */
    protected $name;

    /**
     * @var Factory The factory.
     */
    protected $factory;

    /**
     * @var string The page context.
     */
    protected $context;

    /**
     * @var moodle_url The page url.
     */
    protected $url;

    /**
     * @var string The page layout.
     */
    protected $pagelayout;

    /**
     * Class constructor.
     *
     * @param array $config An associative array of configuration settings. Optional.
     * @param Factory $factory The factory. Optional.
     */
    public function __construct($config = [], Factory $factory = null) {
        // Set the view name.
        if (empty($this->name)) {
            if (array_key_exists('name', $config)) {
                $this->name = $config['name'];
            } else {
                $this->name = $this->get_name();
            }
        }

        if (!array_key_exists('namespace', $config)) {
            throw new \Exception(get_string('errorcontrollernamespace', 'local_registration'));
        }

        $this->factory = $factory ?? new Factory($config['namespace']);
    }

    /**
     * Method to get the controller name.
     *
     * The controller name is set by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor.
     *
     * @return string The name of the controller.
     * @throws \Exception
     */
    public function get_name() {
        if (empty($this->name)) {
            $class = get_class($this);
            $lastslashposition = strrpos($class, '\\');

            if ($lastslashposition === false) {
                throw new \Exception(get_string('errorcontrollergetname', 'local_registration', $class));
            }

            $this->name = substr($class, $lastslashposition + 1);

            if (empty($this->name)) {
                throw new \Exception(get_string('errorcontrollergetname', 'local_registration', $class));
            }
        }

        return $this->name;
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param string $name The model name. Optional.
     * @param array $config Configuration array for model. Optional.
     *
     * @return BaseModel|boolean Model object on success; otherwise false on failure.
     */
    public function get_model($name = '', $config = []) {
        if (empty($name)) {
            $name = $this->get_name();
        }

        $model = $this->factory->create_model($name, $config);

        if ($model === null) {
            return false;
        }

        return $model;
    }

    /**
     * Display method to render the entire page content.
     *
     * This method renders the header, content, and footer of the page.
     *
     * @return void
     */
    public function display(): void {
        $this->display_header();
        $this->display_content();
        $this->display_footer();
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
        global $PAGE, $OUTPUT;

        if (!empty($this->url)) {
            $PAGE->set_url($this->url);
        }

        $namespace = '\core\context\\' . $this->context;
        $PAGE->set_context($namespace::instance());
        $title = $this->get_title();
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
        $PAGE->set_pagelayout($this->pagelayout);
        echo $OUTPUT->header();

        if ($description = $this->get_description()) {
            echo $OUTPUT->box($description);
        }
    }

    /**
     * Displays the footer content using the global OUTPUT object.
     *
     * This function is responsible for rendering and outputting the footer content
     * of the web page. It retrieves the footer content from the global OUTPUT object
     * and echoes it to the output buffer.
     *
     * @return void
     */
    protected function display_footer(): void {
        global $OUTPUT;

        echo $OUTPUT->footer();
    }

    /**
     * Abstract method for displaying the content of the page.
     *
     * This method is responsible for rendering the main content of the page.
     * It should be implemented by concrete subclasses to provide specific content.
     *
     * @return void
     */
    abstract protected function display_content(): void;

    /**
     * Retrieves the title of the page.
     *
     * @return string The title of the page.
     */
    protected function get_title(): string {
        return get_string('pluginname', 'local_registration');
    }

    /**
     * Retrieves the description of the page.
     *
     * @return string|null The description of the page, or null if no description is available.
     */
    protected function get_description(): ?string {
        return get_string('plugindescription', 'local_registration');
    }
}
