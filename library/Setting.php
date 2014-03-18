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

/**
 * Setting class
 *
 * @package Lapurd
 */
class Setting
{
    /**
     * An associative array that holds system settings
     *
     * @var array
     */
    private static $settings = array();

    /**
     * The instance is constructed by loading 'settings.php'
     */
    public function __construct()
    {
        if (file_exists($include = SYSROOT . '/settings.php')) {
            require_once($include);
        } else {
            throw new \LogicException("No 'settings.php' is found!");
        }

        if (!isset($settings)) {
            throw new \LogicException("No settings has been configured!");
        }

        if (!isset($settings['application'])) {
            throw new \LogicException("No application has been configured!");
        }

        self::$settings = $settings;
    }

    /**
     * Query a path from the registry
     *
     * @param string $name
     *   A URL path
     *
     * @return array|null
     *   An array of the path information
     */
    public static function getSetting($name)
    {
        if (isset(self::$settings[$name])) {
            return self::$settings[$name];
        } else {
            return null;
        }
    }

    /**
     * Add a path into the registry
     *
     * @param string $name
     *   A URL path
     * @param array $value
     *   An array of the path information
     *
     *       [
     *           'callback' => '',
     *       ]
     */
    public static function addSetting($name, $value)
    {
        self::$settings[$name] = $value;
    }

    /**
     * Read a setting
     *
     * @param string $name
     *   The name of the setting
     *
     * @return mixed|null
     *   The value of the setting
     */
    public function read($name)
    {
        if (isset(self::$settings[$name])) {
            return self::$settings[$name];
        } else {
            return null;
        }
    }

    /**
     * Write a setting
     *
     * @param $name
     *   The name of the setting
     * @param $value
     *   The value of the setting
     */
    public function write($name, $value)
    {
        self::$settings[$name] = $value;
    }
}
