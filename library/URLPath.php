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
 * URLPath class
 *
 * Handles the URL path (?q=) that is being queried against 'index.php'. It
 * has an internal registry of all the URL paths that the system can response.
 *
 * @package Lapurd
 */
class URLPath
{
    /**
     * A registry of all system paths
     *
     * @var array
     */
    private static $paths = array();

    /**
     * Build the registry by invoking 'paths()' function from all components
     */
    public static function build()
    {
        foreach (Core::get()->getEnabledModules() as $module) {
            $providers[] = Core::getComponent('module', $module);
        }
        $providers[] = Core::getComponent('application', Core::get()->getCurrentApplication());

        foreach ($providers as $provider) {
            if (is_callable($callback = $provider['namespace'] . '\\paths')) {
                $paths = call_user_func($callback);

                foreach ($paths as $path => $info) {
                    $info['provider'] = $provider;
                    URLPath::addPath($path, $info);
                }
            }
        }
    }

    /**
     * Query a path from the registry
     *
     * @param string $path
     *   A URL path
     *
     * @return array|null
     *   An array of the path information
     */
    public static function getPath($path)
    {
        if (isset(self::$paths[$path])) {
            return self::$paths[$path];
        } else {
            return null;
        }
    }

    /**
     * Add a path into the registry
     *
     * @param string $path
     *   A URL path
     * @param array $info
     *   An array of the path information
     *
     *       [
     *           'callback' => '',
     *       ]
     */
    public static function addPath($path, $info)
    {
        self::$paths[$path] = $info;
    }
}
