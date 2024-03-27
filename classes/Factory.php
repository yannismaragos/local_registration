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
 * Factory class for creating instances of classes.
 *
 * This class provides methods for loading various components. It utilizes a
 * simple factory pattern to instantiate these components dynamically.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration;

use local_registration\model\Base as BaseModel;
use Exception;

/**
 * Class factory.
 *
 * @package     local_registration
 * @copyright   2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Factory {
    /**
     * @var string The namespace to create the objects from.
     */
    private $namespace;

    /**
     * Class constructor.
     *
     * @param string $namespace The namespace.
     */
    public function __construct($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * Create an instance of a class within a given namespace and type.
     *
     * This method dynamically constructs a fully-qualified class name based on the provided
     * namespace and type, and then instantiates the corresponding class.
     *
     * @param string $type The type or class name within the specified namespace.
     *
     * @return mixed An instance of the specified class.
     * @throws Exception If the specified class does not exist.
     */
    public function create_instance(string $type) {
        $class = "\\$this->namespace\\$type";

        if (class_exists($class)) {
            return new $class();
        } else {
            throw new Exception("Class $class not found.");
        }
    }

    /**
     * Method to load and return a model object.
     *
     * @param string $name The name of the model.
     * @param array $config Optional configuration array for the model.
     *
     * @return BaseModel The model object.
     * @throws Exception
     */
    public function create_model($name, array $config = []) {
        // Clean the parameters.
        $name = preg_replace('/[^A-Z0-9_]/i', '', $name);

        $class = "\\$this->namespace\\model\\" . ucfirst($name);

        if (class_exists($class)) {
            $model = new $class($config);
        } else {
            throw new Exception("Class $class not found.");
        }

        return $model;
    }
}
