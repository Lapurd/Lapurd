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

namespace Lapurd\Component;

use Lapurd\Component;

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
}
