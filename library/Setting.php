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
    private $settings;

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

        $this->settings = $settings;
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
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
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
        $this->settings[$name] = $value;
    }
}
