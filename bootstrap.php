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
 * Directory of the web root
 */
DEFINE(__NAMESPACE__ . '\\SYSROOT', __DIR__);

/**
 * Directory of the application root
 */
DEFINE(__NAMESPACE__ . '\\APPROOT', __DIR__ . '/application');

/**
 * Autoloader for Lapurd library
 *
 * @param $class
 */
function autoload($class)
{
    $strltrim = function ($string, $prefix) {
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            return substr($string, strlen($prefix));
        } else {
            return $string;
        }
    };

    $name = $strltrim($class, 'Lapurd\\');

    if ($name != $class && file_exists($file = __DIR__ . '/library/' . str_replace('\\', '/', $name) . '.php')) {
        require_once $file;
    }
}

// Register autoloader for Lapurd library
spl_autoload_register(__NAMESPACE__ . '\\autoload');
