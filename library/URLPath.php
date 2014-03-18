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
     * URL path of current instance
     *
     * @var string
     */
    public $path;

    /**
     * A registry of all system paths
     *
     * @var array
     */
    private static $paths = array();

    /**
     * A mask array of all known system paths
     *
     * @see mask
     * @var array
     */
    private static $masks = array();

    /**
     * An array of path aliases
     *
     * @var array
     */
    private static $aliases = array();

    /**
     * @param $path
     *   A URL path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

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
     * @throws \LogicException
     */
    public static function getPath($path)
    {
        if (isset(self::$paths[$path])) {
            $candidates = self::$paths[$path];

            usort($candidates, function ($a, $b) {
                if ($a['provider']->type == 'application' || $b['provider']->type = 'lapurd') {
                    return -1;
                } elseif ($b['provider']->type == 'application' || $a['provider']->type = 'lapurd') {
                    return 1;
                } elseif ($a['provider']->type == 'module' && $b['provider']->type == 'module') {
                    if (isset($a['weight']) && !isset($b['weight'])) {
                        return -1;
                    } elseif (!isset($a['weight']) && isset($b['weight'])) {
                        return 1;
                    } elseif (!isset($a['weight']) && !isset($b['weight'])) {
                        return 0;
                    } else {
                        if ($a['weight'] < $b['weight']) {
                            return 1;
                        } else {
                            return -1;
                        }
                    }
                } else {
                    throw new \LogicException("Unsupported component type!");
                }
            });

            $info = $candidates[0];

            while (isset($info['redirect'])) {
                $info = self::getPath($info['redirect']);
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
     *           'path' => '', // string
     *           'menu' =>
     *               [
     *                   'type' => '', //string
     *                   'name' => '', //string
     *                   'weight' => '', //int
     *               ]
     *           'weight' => '', // int
     *           'handler' =>
     *               [
     *                   'redirect' => '', // string
     *                   'callback' => '', // callable
     *                   'arguments' => [], // array
     *               ]
     *           'page-title' => '', // string
     *       ]
     *
     *   The 'redirect' element is a URL path on which another router is handling.
     *
     *   The integer in 'arguments' array is corresponding to the position in a
     *   URL path. Suppose the 'arguments' array is [arg1, 2, 3, arg4], and the
     *   current URL path is 'path/hello/to/foo/do/bar/something', then the
     *   real callback arguments array will be [arg1, 'foo', 'bar', arg4].
     * @param Component $provider
     *   A component provider
     */
    public static function addPath($path, array $info, Component $provider)
    {
        self::addMask($path);

        $info['provider'] = $provider;

        self::$paths[$path][$provider->namespace] = $info;
    }

    /**
     * Calculate and store the mask
     *
     * @param string $path
     *   A router path to be calculated
     */
    public static function addMask($path)
    {
        $mask = self::mask($path);

        self::$masks[$mask] = 1;
    }

    /**
     * Get all the stored router path masks
     *
     * @return array
     *   An array which contains all the masks
     */
    public static function getMasks()
    {
        $masks = array_keys(self::$masks);

        rsort($masks);

        return $masks;
    }

    /**
     * Calculate the mask of the given path
     *
     * A mask is a binary number which each bit represents a part of the path.
     * If the bit is 0, then it represents the wildcard while 1 not.
     *
     * For example, bit string 1011 represents foo/%/bar1/bar2.
     *
     * @param string $path
     *   A router path to be calculated
     *
     * @return integer
     *   The mask of the path
     */
    public static function mask($path)
    {
        $parts = explode('/', $path);
        $counts = count($parts);
        // We store the highest index of parts here to save some work in the fit
        // calculation loop.
        $slashes = $counts - 1;

        $fit = 0;
        foreach ($parts as $k => $part) {
            if (!preg_match('/^%$/', $part)) {
                $fit |= 1 << ($slashes - $k);
            }
        }
        // If there is no %, it fits maximally.
        if (!$fit) {
            $fit = (1 << $counts) - 1;
        }

        return $fit;
    }

    /**
     * Wrapper around 'self::mask' on '$this->path'
     *
     * @return int
     */
    public function getMask()
    {
        return self::mask($this->path);
    }

    /**
     * Returns the ancestors for any given path
     *
     * For example, the ancestors of foo/12345/bar are:
     *
     * - foo/12345/bar
     * - foo/12345/%
     * - foo/%/bar
     * - foo/%/%
     *
     * Where % means that any argument matches that part.
     *
     * We limit ourselves to using binary numbers that correspond the patterns
     * of wildcards of router items that actually exists.
     *
     * @param $path
     *
     * @return array
     *   An array which contains the ancestors
     */
    public static function ancestors($path)
    {
        $parts = explode('/', $path);
        $counts = count($parts);
        $length = $counts - 1;
        $endmask = (1 << $counts) - 1;

        $ancestors = array();

        $path_masks = self::getMasks();

        // Only examine patterns that actually exist as router items (the masks).
        foreach ($path_masks as $i) {
            if ($i > $endmask) {
                // Only look at masks that are not longer than the path of interest.
                continue;
            }
            $current = '';
            for ($j = $length; $j >= 0; $j--) {
                if ($i & (1 << $j)) {
                    $current .= $parts[$length - $j];
                } else {
                    $current .= '%';
                }
                if ($j) {
                    $current .= '/';
                }
            }
            $ancestors[] = $current;
        }

        return $ancestors;
    }

    /**
     * Wrapper around 'self::ancestors' on '$this->path'
     * @return array
     */
    public function getAncestors()
    {
        return self::ancestors($this->path);
    }

    /**
     * Map a callback arguments
     *
     * Integer elements in $map array are mapped to corresponding elements in
     * $data array according to the value of that integer which represents the
     * index in $data array.
     *
     * For example, if $map is ['view', 1] and $data is ['node', '12345'], then
     * 'view' in $map will not be changed because it is not an integer, but 1
     * in $map will as it is an integer. As $data[1] is '12345', 1 in $map will
     * be replaced with '12345'. So the result will be ['view', '12345'].
     *
     * @param array $map
     *   An array that defines the mapping
     * @param array $data
     *   An array of potential replacements
     *
     * @return array
     *   The $map array unserialized and mapped
     */
    public static function mapParams(array $map, array $data)
    {
        foreach ($map as $k => $v) {
            if (is_int($v)) {
                $map[$k] = isset($data[$v]) ? $data[$v] : '';
            }
        }

        return $map;
    }

    /**
     * Add an alias to a URL path
     *
     * @param string $alias
     *   A URL path alias
     * @param string $path
     *   A URL path
     */
    public static function addAlias($alias, $path)
    {
        self::$aliases[$alias] = $path;
    }

    /**
     * Resolve an alias to the URL path
     *
     * If no URL path is found for the alias, then the alias itself
     * will be returned.
     *
     * @param string $alias
     *   A URL path alias
     *
     * @return string
     */
    public static function resolveAlias($alias)
    {
        if (isset(self::$aliases[$alias])) {
            return self::$aliases[$alias];
        } else {
            return $alias;
        }
    }
}
