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

use Lapurd\Exception\Http as HttpException;

/**
 * Core class
 *
 * It is the main class of the Lapurd framework with a single-instance, one can
 * only use 'Core::get()' to get the instance of the class.
 *
 * @package Lapurd
 */
class Core
{
    /**
     * The only instance of 'Core'
     *
     * @var Core
     */
    private static $obj;

    /**
     * The URL path being queried
     *
     * @var string
     */
    private $path;

    /**
     * The instance of 'Router'
     *
     * @var Router
     */
    private $router;

    /**
     * The instance of 'Lapurd'
     *
     * @var Lapurd
     */
    private $lapurd;

    /**
     * The instance of 'Setting'
     *
     * @var Setting
     */
    private $setting;

    /**
     * An array of 'Module' instances
     *
     * @var Component\Module[]
     */
    private $modules = array();

    /**
     * The instance of the 'Application'
     *
     * @var Component\Application
     */
    private $application;

    /**
     * Constructor of the class
     *
     * By restricting to private access, the class can only be constructed by
     * 'Core::get()' static method from inside, so that we can force only one
     * instance is constructed.
     */
    private function __construct()
    {
        // Register autoloader for Lapurd components
        spl_autoload_register(__NAMESPACE__ . '\\Core::autoload');

        /**
         * Init system settings
         */
        $this->setting = new Setting();

        /**
         * Init Application
         */
        $this->application = self::newComponent('application');

        /**
         * Init Modules
         */
        foreach ($this->getEnabledModules() as $module) {
            $this->modules[$module] = self::newComponent('module', $module);
        };

        /**
         * Init Lapurd
         */
        $this->lapurd = self::newComponent('lapurd');
    }

    /**
     * The only way to get the instance of the class
     *
     * @return Core
     */
    public static function get($new=false)
    {
        if ($new)
        {
            self::$obj = null;
        }

        if (empty(self::$obj)) {
            self::$obj = new Core();
        }

        return self::$obj;
    }

    /**
     * Main execution entrance
     */
    public function run()
    {
        try {
            $this->bootstrap();

            $this->router->run();
        } catch (HttpException $e) {
            $e->sendHeader();
            $e->showErrorPage();
        } catch (\RuntimeException $e) {
            print '<pre>' . $e . '</pre>';
        }
    }

    /**
     * Autoloader for system components
     *
     * @param $class
     *   A fully-qualified class name
     */
    public static function autoload($class)
    {
        $strltrim = function ($string, $prefix) {
            if (substr($string, 0, strlen($prefix)) == $prefix) {
                return substr($string, strlen($prefix));
            } else {
                return $string;
            }
        };

        $prefixes = array(
            'module' => __NAMESPACE__ . '\\Module\\',
            'application' => __NAMESPACE__ . '\\Application\\',
        );

        foreach ($prefixes as $type => $prefix) {
            $str = $strltrim($class, $prefix);
            if ($str != $class) {
                $name = $str;
                break;
            }
        }

        if (!isset($name)) {
            return;
        }

        switch ($type) {
            case 'module':
                if (file_exists($file = APPROOT . '/modules/' . $name . '/' . $name . '.php') ||
                    file_exists($file = LPDROOT . '/modules/' . $name . '/' . $name . '.php')
                ) {
                    require_once $file;
                }
                break;
            case 'application':
                if (file_exists($file = APPROOT . '/' . $name . '.php')) {
                    require_once $file;
                }
                break;
            default:
                break;
        }
    }

    /**
     * The bootstrap process
     *
     * @throws \LogicException
     */
    private function bootstrap()
    {
        /**
         * Build Paths Registry
         */
        URLPath::build();

        $this->path = $this->getPath();

        $this->router = new Router($this->path);

    }

    /**
     * Getter of property 'application'
     *
     * @return Component\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Getter of property 'path'
     *
     * If 'path' property has not been set yet, return the current URL path
     * that is being queried.
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        if (isset($_REQUEST['q'])) {
            $path = rtrim($_REQUEST['q'], '/');
        } else {
            $path = 'index';
        }

        return $path;
    }

    /**
     * Get the instance of a requested module
     *
     * @param string $module
     *   The name of the module
     *
     * @return Component\Module|null
     */
    public function getModule($module)
    {
        if (isset($this->modules[$module])) {
            return $this->modules[$module];
        } else {
            return null;
        }
    }

    /**
     * Getter of property 'lapurd'
     *
     * @return Lapurd
     *   An instance of 'Lapurd' component
     */
    public function getLapurd()
    {
        return $this->lapurd;
    }

    /**
     * @return array
     */
    public function getRouter()
    {
        return $this->router->get();
    }

    /**
     * Getter of property 'modules'
     *
     * @return Component\Module[]
     *   An array of 'Module' instances
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Get a system setting
     *
     * @param string $name
     *   The name of the setting
     *
     * @return mixed
     *   The value of the setting
     */
    public function getSetting($name)
    {
        return $this->setting->read($name);
    }

    /**
     * Set a system setting
     *
     * @param string $name
     *   The name of the setting
     * @param mixed $value
     *   The value of the setting
     */
    public function setSetting($name, $value)
    {
        $this->setting->write($name, $value);
    }

    /**
     * Init a requested component
     *
     * @param string $type
     *   The type of the component
     * @param string|null $name
     *   The name of the component
     *
     * @return Component
     */
    private static function newComponent($type, $name=null)
    {
        $component = self::getComponent($type, $name);

        return self::initComponent($component);
    }

    /**
     * Get a component build array
     *
     * A component is an important concept in Lapurd, it distinguishes
     * different type of components.
     *
     *   [
     *       'name' => '', // name of the component
     *       'type' => '', // type of the component
     *       'class' => '', // main class of the component
     *       'include' => '', // place for component hooks
     *       'filepath' => '', // file path to the component
     *       'namespace' => '', // namespace of the component
     *   ]
     *
     * @param string $type
     *   The type of the component
     * @param string|null $name
     *   The name of the component
     *
     * @return array
     *   A component build array
     *
     * @throws \DomainException
     */
    public static function getComponent($type, $name=null)
    {
        switch ($type) {
            case 'lapurd':
                $refl = new \ReflectionClass(__NAMESPACE__ . '\\Lapurd');

                return array(
                    'name' => __NAMESPACE__,
                    'type' => 'lapurd',
                    'class' => __NAMESPACE__ . '\\Lapurd',
                    'include' => 'lapurd.inc.php',
                    'filepath' => dirname($refl->getFileName()),
                    'namespace' => __NAMESPACE__ . '\\Lapurd',
                );
                break;
            case 'module':
                $refl = new \ReflectionClass(__NAMESPACE__ . '\\Module\\' . $name);

                return array(
                    'name' => $name,
                    'type' => 'module',
                    'class' => __NAMESPACE__ . '\\Module\\' . $name,
                    'include' => 'module.inc.php',
                    'filepath' => dirname($refl->getFileName()),
                    'namespace' => __NAMESPACE__ . '\\Module\\' . $name,
                );
                break;
            case 'application':
                if (is_null($name)) {
                    $name = self::get()->getCurrentApplication();
                }

                $refl = new \ReflectionClass(__NAMESPACE__ . '\\Application\\' . $name);

                return array(
                    'name' => $name,
                    'type' => 'application',
                    'class' => __NAMESPACE__ . '\\Application\\' . $name,
                    'include' => 'application.inc.php',
                    'filepath' => dirname($refl->getFileName()),
                    'namespace' => __NAMESPACE__ . '\\Application\\' . $name,
                );
                break;
            default:
                throw new \DomainException("Unknown component type '$type'!");
                break;
        }
    }

    /**
     * Load a requested component
     *
     * @param array $component
     *   An component build array
     *
     * @return bool
     *   False if no such component is found
     *
     * @throws \DomainException
     */
    private static function loadComponent($component)
    {
        $file = $component['filepath'] . '/' . $component['include'];

        if (file_exists($file)) {
            require_once $file;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Init a requested component
     *
     * @param array $component
     *   An component build array
     *
     * @return Component
     *
     * @throws \LogicException
     */
    public static function initComponent($component)
    {
        if (!self::loadComponent($component)) {
            throw new \LogicException("No component " . $component['type'] . " '" . $component['name'] . "' can be found!");
        }

        if (is_callable($callback = $component['namespace'] . '\\info')) {
            $info = array_merge((array) call_user_func($callback), $component);
        } else {
            $info = $component;
        }

        return new $component['class']($info);
    }

    /**
     * Get all the enabled modules
     *
     * @return array
     */
    public function getEnabledModules()
    {
        return $this->getSetting('modules');
    }

    /**
     * Get the current running application
     *
     * @return string
     */
    public function getCurrentApplication()
    {
        return $this->getSetting('application');
    }
}
