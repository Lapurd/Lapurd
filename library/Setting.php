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
        if (file_exists($include = self::getConfPath() . '/settings.php')) {
            require($include);
        } else {
            throw new \LogicException("No 'settings.php' is found!");
        }

        if (!isset($settings)) {
            throw new \LogicException("No settings has been configured!");
        }

        if (!isset($settings['application'])) {
            throw new \LogicException("No application has been configured!");
        }
        if (!isset($settings['base_url'])) {
            $settings['base_url'] = self::getBaseURL();
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

    /**
     * Get the base URL
     *
     * @return string
     */
    public static function getBaseURL()
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'];

        // In contrast to $_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME'] can not
        // be modified by a visitor.
        if ($base_dir = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
            $base_url .= '/' . $base_dir;
        }

        return $base_url;
    }

    /**
     * Find the appropriate configuration directory.
     *
     * Try finding a matching configuration directory by stripping the website's
     * hostname from left to right and pathname from right to left. The first
     * configuration file found will be used; the remaining will ignored. If no
     * configuration file is found, return a default value '$conf_path/default'.
     *
     * Example for a fictitious site installed at
     * http://www.example.domain:8080/site/test/ the 'settings.php' is searched
     * in the following directories:
     *
     *  1. $conf_path/8080.www.example.domain.site.test
     *  2. $conf_path/www.example.domain.site.test
     *  3. $conf_path/drupal.org.site.test
     *  4. $conf_path/org.site.test
     *
     *  5. $conf_path/8080.www.example.domain.site
     *  6. $conf_path/www.example.domain.site
     *  7. $conf_path/drupal.org.site
     *  8. $conf_path/org.site
     *
     *  9. $conf_path/8080.www.example.domain
     * 10. $conf_path/www.example.domain
     * 11. $conf_path/drupal.org
     * 12. $conf_path/org
     *
     * 13. $conf_path/default
     *
     * @param bool $reset
     *   Force a full search for matching directories even if one had been
     *   found previously.
     *
     * @return string
     *   The path of the matching directory.
     */
    public static function getConfPath($reset = false)
    {
        static $conf_path = '';

        if ($conf_path && !$reset) {
            return $conf_path;
        }

        $path = SYSROOT . '/domains';
        $server = explode('.', implode('.', array_reverse(explode(':', rtrim($_SERVER['HTTP_HOST'], '.')))));
        $uri_path = explode('/', $_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['SCRIPT_FILENAME']);
        for ($i = count($uri_path) - 1; $i > 0; $i--) {
            for ($j = count($server); $j > 0; $j--) {
                $dir = implode('.', array_slice($server, -$j)) . implode('.', array_slice($uri_path, 0, $i));
                if (file_exists("$path/$dir/settings.inc.php")) {
                    $conf_path = "$path/$dir";

                    return $conf_path;
                }
            }
        }

        $conf_path = SYSROOT . '/domains/default';

        return $conf_path;
    }
}
