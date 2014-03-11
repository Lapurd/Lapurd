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

namespace Lapurd\Lapurd;

/**
 * URL paths that the system provides as fallback
 *
 * @return array
 */
function paths()
{
    return array(
        'index' => array(
            'callback' => 'sayHelloWorld',
            'page-title' => 'Hello World',
        ),
    );
}

/**
 * System's 'views' hook
 *
 * @return array
 */
function views()
{
    return array(
        /**
         * The core 'page' view.
         */
        'page',

        /**
         *
         */
        'page-not-found',

        /**
         * The core 'block' view.
         */
        'block',

        /**
         * The core 'block' view.
         */
        'region',
    );
}

/**
 * System's 'view_hook_render' hook on 'page' view
 *
 * @param View $view
 *   A View object
 */
function view_page_render(View $view)
{
    // website logo
    if (!Asset::getAsset('logo', false)) {
        Asset::addAsset('logo', LPDROOT . '/views/assets/logo.png');
    }
    $logo = new Asset('logo');
    $view->setVariable('logo', $logo->getURL());

    // website favicon
    if (!Asset::getAsset('favicon', false)) {
        Asset::addAsset('favicon', LPDROOT . '/views/assets/favicon.ico');
    }
    $favicon = new Asset('favicon');
    $view->setVariable('favicon', $favicon->getHTML());

    // website page title
    if (!$view->getVariable('page_title')) {
        $router = Core::get()->getRouter();
        if (isset($router['page-title'])) {
            $view->setVariable('page_title', $router['page-title']);
        } else {
            $view->setVariable('page_title', 'Hello World');
        }
    }
}
