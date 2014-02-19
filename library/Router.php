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

use Lapurd\Exception\Http\PageNotFound as PageNotFoundException;

/**
 * Class Router
 *
 * A Router handles a URL path request, find the corresponding handler for that
 * request execute it and print the output.
 *
 * @package Lapurd
 */
class Router
{
    /**
     * A URL path
     *
     * @var string
     */
    private $path;

    /**
     * A URL path info array
     *
     * @var array
     */
    private $router;

    /**
     * Constructor
     *
     * @param string $path
     *   A URL path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->init();
    }

    /**
     * Get the URL path info array
     *
     * @return array
     *   An array contains the URL path information
     */
    public function get()
    {
        return $this->router;
    }

    /**
     * Execute the handler
     */
    public function run()
    {
        self::call($this->router);
    }

    /**
     * Init the router and find the handler
     *
     * @throws Exception\Http\PageNotFound
     */
    private function init()
    {
        $router = URLPath::getPath($this->path);

        if (!isset($router)) {
            throw new PageNotFoundException();
        }

        $this->router = $router;
    }

    /**
     * Call a callable array and print the result
     *
     *     [
     *         'provider' => [], // A provider info array
     *         'callback' => '', // A callback function
     *     ]
     * @param $callable array
     */
    public static function call($callable)
    {
        call_user_func(array(Core::initComponent($callable['provider']), $callable['callback']));
    }
}
