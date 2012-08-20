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
 * Application class
 *
 * An application is a system component that consists of several modules
 * and themes with configurations, which could be run as a complete website.
 *
 * @package Lapurd
 */
abstract class Application extends Component
{
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

        if (is_callable($callback = Core::getComponent('application', Core::get()->getCurrentApplication())['namespace'] . '\\modules')) {
            Core::get()->setSetting('modules', call_user_func($callback));
        }
    }
}
