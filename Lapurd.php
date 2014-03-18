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
    public static function get($name=null)
    {
        return parent::get($name);
    }

    public static function info($name=null)
    {
        $refl = new \ReflectionClass('Lapurd\\Component\\Lapurd');

        return array(
            'name' => 'Lapurd',
            'type' => 'lapurd',
            'class' => 'Lapurd\\Component\\Lapurd',
            'include' => 'lapurd.inc.php',
            'filepath' => dirname($refl->getFileName()),
            'namespace' => 'Lapurd\\Component\\Lapurd',
        );
    }

    /**
     * Default handler for 'index'
     */
    public static function autoload($class) {}

    public function sayHelloWorld()
    {
        echo "<h1>Hello World!</h1>";
    }
}
