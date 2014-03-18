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
 * Lapurd class
 *
 * @package Lapurd
 */
final class Lapurd extends Component
{
    /**
     * Default handler for 'index'
     */
    public function sayHelloWorld()
    {
        echo "<h1>Hello World!</h1>";
    }

    /**
     * Handler if no other handler can be found
     */
    public function handlePageNotFound()
    {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 Page Not Found</h1>";
        echo "The page you requested can not be found.";
    }
}
