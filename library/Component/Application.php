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

use Lapurd\Core;
use Lapurd\Setting;
use Lapurd\Component;

/**
 * Application class
 *
 * An application is a system component that consists of several modules
 * and themes with configurations, which could be run as a complete website.
 *
 * @package Lapurd
 */
abstract class Application extends Component
{
    public static function info($name)
    {
        $refl = new \ReflectionClass('Lapurd\\Application\\' . $name);

        return array(
            'name' => $name,
            'type' => 'application',
            'class' => 'Lapurd\\Application\\' . $name,
            'include' => 'application.inc.php',
            'filepath' => dirname($refl->getFileName()),
            'namespace' => 'Lapurd\\Application\\' . $name,
        );
    }

    public static function autoload($class)
    {
        $prefix = 'Lapurd\\Application\\';

        if (substr($class, 0, strlen($prefix)) != $prefix) {
            return;
        }

        $name = substr($class, strlen($prefix));

        if (is_dir(\Lapurd\SYSROOT . '/applications/' . $name)) {
            $path =  \Lapurd\SYSROOT . '/applications/' . $name;
        } elseif ($app_path = \Lapurd\SYSROOT == \Lapurd\LPDROOT) {
            $path = \Lapurd\SYSROOT . '/application';
        } else {
            $path = \Lapurd\SYSROOT;
        }

        if ($path) {
            require_once $path . '/' . $name . '.php';
        }
    }

    /**
     * Constructor
     *
     * If there is a 'modules()' function defined inside the application's
     * namespace, then it will be called to get the enabled modules information.
     *
     * @param array $info
     */
    public function __construct($info)
    {
        parent::__construct($info);

        if (isset($info['theme'])) {
            Core::get()->setSetting('theme', $info['theme']);
        }
        if (isset($info['modules'])) {
            Core::get()->setSetting('modules', $info['modules']);
        }
    }
}
