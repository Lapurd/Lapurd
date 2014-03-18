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
 * Module class
 *
 * @package Lapurd
 */
abstract class Module extends Component
{
    public static function info($name)
    {
        $refl = new \ReflectionClass('Lapurd\\Module\\' . $name);

        return array(
            'name' => $name,
            'type' => 'module',
            'class' => 'Lapurd\\Module\\' . $name,
            'include' => 'module.inc.php',
            'filepath' => dirname($refl->getFileName()),
            'namespace' => 'Lapurd\\Module\\' . $name,
        );
    }

    public static function autoload($class)
    {
        $prefix = 'Lapurd\\Module\\';

        if (substr($class, 0, strlen($prefix)) != $prefix) {
            return;
        }

        $name = substr($class, strlen($prefix));

        $approot = Application::info()['filepath'];

        if (is_dir($path = $approot . '/modules/' . $name) ||
            is_dir($path = \Lapurd\LPDROOT . '/modules/' . $name)
        ) {
            require_once $path . '/' . $name . '.php';
        }
    }
}
