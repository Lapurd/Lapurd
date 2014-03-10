<?php
/*
 * This file is part of the Lapurd package.
 *
 * (c) Techlive Zheng <techlivezheng@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lapurd;

/**
 * Class of theme component
 *
 * @package Lapurd
 */
abstract class Theme extends Component
{
    /**
     * Render a page
     *
     * @param string $content
     *
     * @return string
     */
    public function render($content)
    {
        $router = Core::get()->getRouter();

        $view = new View('page');

        /**
         * A template named with current URL path has higher priority.
         *
         * For example:
         *     page--index.tpl.php
         */
        $view->addSchema(
            preg_replace('/[\/]+/', '-', strtolower($router['path'])),
            $router['provider']
        );

        return $view->theme($content);
    }
}
