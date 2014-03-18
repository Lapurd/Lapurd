<?php
namespace Lapurd;

/**
 * Class View
 *
 * @package Lapurd
 *
 * A View is the very basic themable element in Lapurd, it can be themed by a
 * template.
 *
 * The template can have several names with different priority. Other providers
 * can add naming schemas to a view, as long as they provide the corresponded
 * templates. The provider of the view mush have a template named the same,
 * which will be used if no other template with higher priority is found,
 * provided in its 'views/' directory.
 *
 * Lapurd tries to find a proper template for a view in the 'views/' directory
 * of the current application first. If none is found, it will fallback to the
 * template provided by its provider.
 *
 */
class View
{
    private $name;

    private static $views = array();

    private $assets = array();

    private $schemas;

    private $template;

    private $provider;

    private $variables = array();

    /**
     * @param string $name
     *   The name of a view that is in the views registr
     */
    public function __construct($name)
    {
        if (!$info = self::getView($name)) {
            throw new \LogicException("View '$name' can not be found!");
        }
        $this->name = $info['name'];
        $this->provider = $info['provider'];
        $this->template = new Template();
    }

    public static function build()
    {
        Core::invokeAll('views', array(), function ($views, $provider) {
            foreach ($views as $view) {
                View::addView($view, $provider);
            }
        });
    }

    /**
     * Get a view from the views registry
     *
     * @param string $name
     *   The name of the view
     *
     * @return array|null
     *   The information array of the view
     */
    public static function getView($name)
    {
        if (isset(self::$views[$name])) {
            return self::$views[$name];
        } else {
            return null;
        }
    }

    /**
     * Add a view into the views registry
     *
     * @param string $name
     *   The name of the view
     * @param Component $provider
     *   A component provider
     */
    public static function addView($name, Component $provider)
    {
        self::$views[$name] = array(
            'name' => $name,
            'provider' => $provider,
        );
    }

    /**
     * Theme a view
     *
     * @param string $content
     *   The main content of the view
     *
     * @return string
     *   The HTML result after the theming
     */
    public function theme($content='')
    {
        /**
         * Hook to alter the schemas
         */
        Core::invokeAll('view_' . $this->name . '_schemas', array(&$this->schemas));

        /**
         * Prepare templates
         */
        $candidates = array();
        if (isset($this->schemas)) {
            usort($this->schemas, function ($a, $b) {
                if ($a['weight'] == $b['weight']) {
                    return 0;
                }

                return ($a['weight'] > $b['weight']) ? -1 : 1;
            });
            foreach ($this->schemas as $schema) {
                $candidates[] = array(
                    'schema' => $this->name . '--' . $schema['schema'],
                    'provider' => $schema['provider'],
                    'template' => array(
                        'filename' => $this->name . '--' . $schema['schema'] . '.tpl.php',
                        'providers' => self::getProviders($schema['provider']),
                    ),
                );
            }
        }
        $candidates[] = array(
            'schema' => $this->name,
            'provider' => $this->provider,
            'template' => array(
                'filename' => $this->name . '.tpl.php',
                'providers' => self::getProviders($this->provider),
            ),
        );

        /**
         * Find a proper template
         */
        foreach ($candidates as $candidate) {
            foreach ($candidate['template']['providers'] as $template_provider) {
                if (file_exists($template_filepath = $template_provider->filepath . '/views/' . $candidate['template']['filename'])) {
                    $template = $candidate['template'];
                    $template['provider'] = $template_provider;
                    $template['filepath'] = $template_filepath;
                    break 2;
                }
            }
        }

        if (!isset($template)) {
            throw new \LogicException("No template can be found!");
        }

        /**
         * Allow modification before the rendering
         */

        // Let the provider of the view make modifications.
        Core::invoke('view_' . str_replace('-', '_', $this->name) . '_render', $this->provider, array($this));

        // Give the current theme a chance to modify the view.
        Core::invoke('view_' . str_replace('-', '_', $this->name) . '_render', Core::get()->getTheme(), array($this));

        // The application should be able to modify the view as well.
        Core::invoke('view_' . str_replace('-', '_', $this->name) . '_render', Core::get()->getApplication(), array($this));

        // If the name schema used by the template is not same with the name
        // of the view, run all the hooks on the new name schema.
        if ($candidate['schema'] != $this->name) {
            // Let the provider of the new name schema make modifications.
            Core::invoke('view_' . str_replace('-', '_', $candidate['schema']) . '_render', $candidate['provider'], array($this));

            // Give the current theme a chance to modify for the new name schema.
            if (Core::get()->getTheme()->namespace != $candidate['provider']->namespace) {
                Core::invoke('view_' . str_replace('-', '_', $candidate['schema']) . '_render', Core::get()->getTheme(), array($this));
            } else {
                throw new \LogicException('Themes are not supposed to add name schemas!');
            }

            // If the new name schema is not provided by the application, then the
            // application should be able to modify the view as well.
            if (Core::get()->getApplication()->namespace != $candidate['provider']->namespace) {
                Core::invoke('view_' . str_replace('-', '_', $candidate['schema']) . '_render', Core::get()->getApplication(), array($this));
            }
        }

        /**
         * Render the template
         */
        $this->setVariable('assets', Asset::dump(array_keys($this->assets)));
        $this->setVariable('content', $content);
        $this->setVariable('base_url', Core::get()->getBaseURL());

        $output = $this->template->render($template['filepath'], $this->variables);

        return $output;
    }

    /**
     * Add a template naming schema
     *
     * @param string $schema
     *   A naming schema that the template might use
     * @param Component $provider
     *   The component provider of this naming schema
     * @param int $weight
     *   An integer that indicates the priority
     *
     *   There must be a template with the following name placed inside the
     *   'views/' directory of this provider.
     *
     *       $this->name . '--' . $schema . '.tpl.php'
     */
    public function addSchema($schema, Component $provider, $weight = 0)
    {
        $this->schemas[] = array(
            'schema' => $schema,
            'weight' => $weight,
            'provider' => $provider,
        );
    }

    /**
     * Add a template variable
     *
     * @param string $variable
     *   The name of the template variable
     * @param mixed $content
     *   The content of the template variable
     */
    public function setVariable($variable, $content)
    {
        $this->variables[$variable] = $content;
    }

    /**
     * Get a template variable
     *
     * @param string $variable
     *   The name of the template variable
     *
     * @return mixed|null
     *   The content of the template variable
     */
    public function getVariable($variable)
    {
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        } else {
            return null;
        }
    }

    /**
     * Use an asset in the view
     *
     * The asset must exist in the assets registry
     *
     * @param string $asset
     *   The name of the asset to be used
     */
    public function importAsset($asset)
    {
        $this->assets[$asset] = 1;
    }

    /**
     * Remove a specific asset from the view
     *
     * @param string $asset
     *   The name of the asset to be used
     */
    public function removeAsset($asset)
    {
        unset($this->assets[$asset]);
    }

    /**
     * Use an collection of assets in the view
     *
     * @param array $assets
     *   An array of asset names to be used
     */
    public function importAssets(array $assets)
    {
        foreach ($assets as $asset) {
            $this->assets[$asset] = 1;
        }
    }

    /**
     * Get all the possible locations for a template
     *
     * @param Component $provider
     *   A component provider
     *
     * @return array
     */
    private static function getProviders(Component $provider)
    {
        $providers = array();
        // 'views' directory of the application
        $providers[] = Core::get()->getApplication();
        // 'views' directory of the current theme
        $providers[] = Core::get()->getTheme();
        // 'views' directory of the view's provider
        if ($provider->namespace != Core::get()->getApplication()->namespace) {
            $providers[] = $provider;
        }

        return $providers;
    }
}
