<?php
/**
 * This file is part of the Lapurd package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     Techlive Zheng <techlivezheng@gmail.com>
 * @package    Lapurd
 */

namespace Lapurd;

interface ComponentInterface
{
    public static function get($name);

    public static function info($name);

    public static function autoload($name);
}

/**
 * Component class
 *
 * Defines the basic methods of all the system components.
 *
 * A component could be an application, a module or a theme, the system even
 * provides a 'Lapurd' component itself. It could implement system hooks and
 * response to requests.
 *
 * @package Lapurd
 */
abstract class Component implements ComponentInterface
{
    static $components;

    public static function get($name)
    {
        // TODO can not be called from here

        $info = static::info($name);

        $namespace = $info['namespace'];

        if (isset(self::$components[$namespace])) {
            return self::$components[$namespace];
        }

        if (!empty($info['include']) && !self::load($info)) {
            throw new \LogicException("No " . $info['type'] . " '" . $info['name'] . "' can be found!");
        }

        if (!is_callable($callback = $namespace . '\\info')) {
            throw new \BadFunctionCallException("No 'info()' function is defined");
        }

        $info = array_merge((array) call_user_func($callback), $info);

        $component = new $info['class']($info);

        self::$components[$namespace] = $component;

        return $component;
    }

    private static function load($info)
    {
        if (file_exists($file = $info['filepath'] . '/' . $info['include'])) {
            require_once $file;

            return true;
        } else {
            return false;
        }
    }

    /**
     * The name of the component
     *
     * @var string
     */
    private $name;

    /**
     * The info array of the component
     *
     * This array was retrieved from the 'info()' function defined in the
     * component's namespace. It is need to construct the component instance.
     *
     * @var array
     */
    private $info;

    /**
     * Constructor of the component
     *
     * A component must be constructed with an info array retrieved from the
     * 'info()' function defined in the component's namespace, which describes
     * the basic information about the component.
     *
     * @param array $info
     *
     * @throws \OutOfRangeException
     */
    protected function __construct($info)
    {
        $this->name = $info['name'];
        $this->info = $info;
    }

    /**
     * Getter of class properties
     *
     * If no property is found, it will try the 'info' array.
     *
     * @param string $property
     *   The name of the property
     *
     * @return mixed
     *   The value of the property
     *
     * @throws \LogicException
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        } elseif (isset($this->info[$property])) {
            return $this->info[$property];
        } else {
            throw new \LogicException("No property can be found!");
        }
    }

    public function __isset($property)
    {
        if (property_exists($this, $property)) {
            return true;
        } elseif (isset($this->info[$property])) {
            return true;
        } else {
            return false;
        }
    }
}
