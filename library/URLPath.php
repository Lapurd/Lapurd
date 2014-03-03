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
     * Maximum parts a URL path could be split
     */
    const MAX_PARTS = 7;

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
     *           'arguments' => [], // array
     *       ]
     *
     *   The integer in 'arguments' array is corresponding to the position in a
     *   URL path. Suppose the 'arguments' array is [arg1, 2, 3, arg4], and the
     *   current URL path is 'path/hello/to/foo/do/bar/something', then the
     *   real callback arguments array will be [arg1, 'foo', 'bar', arg4].
     * @param array $provider
     *   A component provider
     */
    public static function addPath($path, array $info, array $provider)
    {
        self::addMask($path);

        $info['provider'] = $provider;

        self::$paths[$path][$provider['namespace']] = $info;
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
        $args = explode('/', $path);
        $parts = array_slice($args, 0, self::MAX_PARTS);
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
        $args = explode('/', $path);
        $parts = array_slice($args, 0, self::MAX_PARTS);
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
}
