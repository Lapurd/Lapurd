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
        Core::invokeAll('paths', array(), function ($paths, $provider) {
            foreach ($paths as $path => $info) {
                self::addPath($path, $info, $provider);
            }
        });
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
            $info = array();

            $candidates = self::$paths[$path];
            foreach ($candidates as $candidate) {
                if ($candidate['provider']['type'] == 'application') {
                    $info = $candidate;
                    break;
                } elseif ($candidate['provider']['type'] == 'module') {
                    if (empty($info)) {
                        $info = $candidate;
                    } elseif (isset($candidate['weight'])) {
                        if (!isset($info['weight']) || $info['weight'] < $candidate['weight']) {
                            $info = $candidate;
                        }
                    }
                } else {
                    throw new \LogicException("Unsupported component type '" . $candidate['provider']['type'] ."'!");
                }
            }

            return $info;
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
     *           'weight' => '', // int
     *           'callback' => '', // callable
     *       ]
     * @param array $provider
     *   A component provider
     */
    public static function addPath($path, array $info, array $provider)
    {
        $info['provider'] = $provider;

        self::$paths[$path][$provider['namespace']] = $info;
    }
}
