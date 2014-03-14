<?php
namespace Lapurd;

class Block
{
    private static $blocks = array();

    public static function build()
    {
        Core::invokeAll('blocks', array(), function ($blocks, $provider) {
            foreach ($blocks as $name => $block) {
                self::addBlock($name, $block, $provider);
            }
        });
    }

    /**
     * Get a block
     *
     * @param string $name
     *   The name of the block
     *
     * @return array
     *   A block information array
     */
    public static function getBlock($name)
    {
        if (isset(self::$blocks[$name])) {
            return self::$blocks[$name];
        } else {
            throw new \LogicException ("Block '$name' can not be found!");
        }
    }

    /**
     * Add a block
     *
     * @param string $name
     *   The name of the block
     * @param array $block
     *   A block information array
     *   [
     *       'id' => '', // string, HTML element id
     *       'classes' => [], // array, HTML element classes
     *       'content' => '' or [
     *           'callback' => '', // callable, A callback where the content of the block comes from
     *           'arguments' => [], // array, A array of arguments mapping for the callback
     *       ]
     *   ]
     * @param array $provider
     *   A component provider
     */
    public static function addBlock($name, array $block, array $provider)
    {
        $block['name'] = $name;
        $block['provider'] = $provider;

        self::$blocks[$name] = $block;
    }

    private $name;

    private $on_paths = array();

    private $off_paths = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Render a block
     *
     * @return string
     */
    public function render()
    {
        $path = Core::get()->getPath();

        if (!empty($this->on_paths)) {
            if (!array_key_exists($path, $this->on_paths)) {
                return;
            }
        } elseif (!empty($this->off_paths)) {
            if (array_key_exists($path, $this->off_paths)) {
                return;
            }
        }

        $block = self::getBlock($this->name);

        if (!isset($block['content'])) {
            throw new \LogicException('No content for the block!');
        }

        if (is_array($block['content'])) {
            $block['content']['provider'] = $block['provider'];
            $content = Router::call($block['content']);
        } elseif (is_string($block['content'])) {
            $content = $block['content'];
        } else {
            throw new \LogicException("Invalid block content format!");
        }

        $view = new View('block');

        /**
         * A template named with the HTML element id of the block has higher
         * priority.
         *
         * For example:
         *     block--login-form.tpl.php
         */
        if (isset($block['id'])) {
            $view->addSchema(
                preg_replace('/[_]+/', '-', strtolower($block['id'])),
                $block['provider']
            );
        }

        /**
         * A template named with the name of the block has less higher priority.
         *
         * For example:
         *     block--block-login.tpl.php
         */
        $view->addSchema(
            preg_replace('/[_]+/', '-', strtolower($block['name'])),
            $block['provider']
        );

        return $view->theme($content);
    }

    /**
     * Show block only on the given path
     *
     * @param string $path
     *   A URL path query
     */
    public function showOnPath($path)
    {
        $this->on_paths[$path] = 1;
    }

    /**
     * Don't show block on the given path
     *
     * @param string $path
     *   A URL path query
     */
    public function showOffPath($path)
    {
        $this->off_paths[$path] = 1;
    }
}
