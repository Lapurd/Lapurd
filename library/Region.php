<?php
namespace Lapurd;

class Region
{
    private $name;

    private $blocks = array();

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
}
