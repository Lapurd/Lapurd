<?php
namespace Lapurd;

class Asset
{
    private $name;

    private $type;

    private $path;

    private static $assets = array();

    public function __construct($name)
    {
        $info = self::getAsset($name);
        $this->name = $info['name'];
        $this->type = $info['type'];
        $this->path = $info['path'];
    }

    /**
     * Add a asset
     *
     * @param string $name
     *   The name of the asset
     * @param string $asset
     *   The file path to the asset
     */
    public static function addAsset($name, $asset)
    {
        if (file_exists($asset)) {
            $path = str_replace(SYSROOT . '/', '', realpath($asset));
        } else {
            throw new \LogicException("Asset '$asset' can not be located!");
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'jpg':
            case 'png':
                $type = 'img';
                break;
            case 'css':
                $type = 'css';
                break;
            case 'js':
                $type = 'js';
                break;
            default:
                throw new \LogicException("Asset '$asset' can not be recognised!");
                break;
        }

        self::$assets[$name] = array(
            'name' => $name,
            'type' => $type,
            'path' => $path,
        );
    }

    /**
     * Get a asset
     *
     * @param $name
     *   The name of the asset
     *
     * @return array|null
     */
    public static function getAsset($name)
    {
        if (isset(self::$assets[$name])) {
            return self::$assets[$name];
        } else {
            return null;
        }
    }

    /**
     * Get the URL to a asset
     *
     * Only js/css/img is supported for now.
     *
     * TODO: If the asset is a less/sass file, the URL to the compiled file will
     * be returned.
     *
     * @return string
     */
    public function getURL()
    {
        return Core::get()->getBaseURL() . '/' . $this->path;
    }

    /**
     * Return the HTML for a asset
     *
     * TODO: Add more filters here: less/sass compiler, image manipulator
     * TODO: Add ability to add attributes to the html element
     *
     * @return string
     */
    public function getHTML()
    {
        switch ($this->type) {
            case 'img':
                return '<img href="' . $this->getURL() . '" />';
                break;
            case 'css':
                return '<link rel="stylesheet" href="' . $this->getURL() . '" />';
                break;
            case 'js':
                return '<script type="text/javascript" src="' . $this->getURL() . '"></script>';
                break;
            default:
                throw new \LogicException("Asset type '$this->type' is not supported!'");
                break;
        }
    }

    /**
     * Dump the required assets as HTML
     *
     * TODO: Add css/js minifier here
     *
     * @param array $assets
     *   An array of asset names to be dumped
     *
     * @return string
     */
    public static function dump(array $assets)
    {
        $return = '';

        foreach ($assets as $asset) {
            $asset_obj = new Asset($asset);
            $return .= $asset_obj->getHTML();
        }

        return $return;
    }
}
