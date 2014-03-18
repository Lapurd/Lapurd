<?php
/*
 * This file is part of the Lapurd package.
 *
 * (c) Techlive Zheng <techlivezheng@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lapurd\Component;

use Lapurd\Core;
use Lapurd\View;
use Lapurd\Region;
use Lapurd\Component;

/**
 * Class of theme component
 *
 * @package Lapurd
 */
abstract class Theme extends Component
{
    public static function get($name=null)
    {
        if (is_null($name)) {
            $name = Core::get()->getCurrentTheme();
        }

        return parent::get($name);
    }

    public static function info($name=null)
    {
        if (is_null($name)) {
            $name = Core::get()->getCurrentTheme();
        }

        $refl = new \ReflectionClass('Lapurd\\Theme\\' . $name);

        return array(
            'name' => $name,
            'type' => 'theme',
            'class' => 'Lapurd\\Theme\\' . $name,
            'include' => 'theme.inc.php',
            'filepath' => dirname($refl->getFileName()),
            'namespace' => 'Lapurd\\Theme\\' . $name,
        );
    }

    public static function autoload($class)
    {
        $prefix = 'Lapurd\\Theme\\';

        if (substr($class, 0, strlen($prefix)) != $prefix) {
            return;
        }

        $name = substr($class, strlen($prefix));

        $approot = Application::info()['filepath'];

        if (is_dir($path = $approot . '/themes/' . $name) ||
            is_dir($path = \Lapurd\LPDROOT . '/themes/' . $name)
        ) {
            require_once $path . '/' . $name . '.php';
        }
    }

    private $regions = array();

    public function __construct($info)
    {
        parent::__construct($info);

        if (is_array($info['regions'])) {
            foreach ($info['regions'] as $region) {
                $this->addRegion(new Region($region));
            }
        }
    }

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

        if (isset($router['themable']) && !$router['themable']) {
            return $content;
        }

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

        // website page title
        if (isset($router['page-title'])) {
            $view->setVariable('page_title', $router['page-title']);
        } else {
            $view->setVariable('page_title', 'Hello World');
            Core::get()->setMessage('warning', "No page title found, set it to 'Hello World'!");
        }

        $regions = array();
        foreach ($this->regions as $region) {
            $regions[$region->getName()] = $region->render();
        }
        $view->setVariable('regions', $regions);

        return $view->theme($content);
    }

    /**
     * Get a region
     *
     * @param string $region
     *   The name of the region
     *
     * @return array|null
     *   A array of blocks to be shown in the region
     */
    public function getRegion($region)
    {
        if (!isset($this->regions[$region])) {
            throw new \LogicException("Region '$region' can not be found!'");
        }

        return $this->regions[$region];
    }

    public function addRegion(Region $region)
    {
        $this->regions[$region->getName()] = $region;
    }
}
