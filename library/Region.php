<?php
namespace Lapurd;

class Region
{
    private $name;

    private $blocks = array();

    private $on_paths = array();

    private $off_paths = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Render a region
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

        $content = '';

        foreach ($this->blocks as $block) {
            $content .= $block->render();
        }

        $view = new View('region');

        $view->addSchema(
            preg_replace('/[_]+/', '-', strtolower($this->name)),
            Core::getComponent('theme')
        );

        return $view->theme($content);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Add a block into a region
     *
     * @param string $block
     *   The name of the block to be added
     */
    public function addBlock($block)
    {
        if (!Block::getBlock($block)) {
            throw new \LogicException("Block '$block' can not be found!'");
        }

        $this->blocks[] = new Block($block);
    }

    /**
     * Show region only on the given path
     *
     * @param string $region
     *   The name of the region
     * @param string $path
     *   A URL path query
     */
    public function showOnPath($region, $path)
    {
        $this->on_paths[$path] = 1;
    }

    /**
     * Don't show region on the given path
     *
     * @param string $region
     *   The name of the region
     * @param string $path
     *   A URL path query
     */
    public function showOffPath($region, $path)
    {
        $this->off_paths[$path] = 1;
    }

}
