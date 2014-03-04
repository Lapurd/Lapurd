<?php
namespace Lapurd;

class Template
{
    /**
     * Render a template
     *
     * @param string $template
     *   File path to the template
     * @param array $variables
     *   An array of variables to be used
     *
     * @return string
     *   The HTML result of the rendering
     */
    public function render($template, $variables)
    {
        extract($variables, EXTR_SKIP);  // Extract the variables
        ob_start();                      // Start output buffering
        include "$template";             // Include the template file
        $contents = ob_get_contents();   // Get the contents of the buffer
        ob_end_clean();                  // End buffering and discard

        return $contents;
    }

}
