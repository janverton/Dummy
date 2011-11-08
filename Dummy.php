<?php

/**
 * This class acts as a template engine, somewhat like the Smarty framework,
 * but much more lightweight.
 * 
 * @category Dummy
 * @package  Dummy
 * @author   Jan Verton <janverton@gmail.com>
 * @license  The MIT License
 * @link     http://github.com/janverton/Dummy
 */
class Dummy
{

    /**
     * Pattern where template loading is matched against
     * 
     * @access public
     */
    const TEMPLATE_PATTERN = '/{load [a-zA-Z0-9_]+\.tpl}/';


    /**
     * Loaded templates
     * 
     * @var array
     * @access protected
     */
    protected $templates = array();

    /**
     * Get the contents of a template
     * 
     * @param String $filename Location of the template file
     * 
     * @example $templateContent = $dummy->getTemplate('foo.tpl');
     *   => 'Hello World!'
     * 
     * @return String The template content
     * 
     * @access public
     */
    public function getTemplate($filename)
    {

        // Get the template when it has not been loaded yet
        if (!array_key_exists($filename, $this->templates)) {
            $this->templates[$filename] = $this->loadTemplate($filename);

            // Get any nested templates
            $this->loadNestedTemplates($filename);
        }

        // Return the template
        return $this->templates[$filename];

    }

    /**
     * Replace a variable with the given value
     * 
     * @param string $name  Name of the variable to replace
     * @param string $value The value to display
     * 
     * @example $dummy->replace('foo', 'bar'); Replaces all template tags named
     *   'foo' with the value 'bar'
     * 
     * @return boolean True on success or false on failure
     * 
     * @access public
     */
    public function replace($name, $value)
    {
        
        // The variable name to replace
        $variableName = '{' . $name . '}';

        // Replace the variable in each template
        foreach ($this->templates as $key => $template) {

            // Replace the var with the give value
            $newTemplate = str_replace($variableName, $value, $template);

            // And save the new template content
            $this->templates[$key] = $newTemplate;
        }

        return true;

    }

    /**
     * Load the template file contents
     * 
     * @param strin $filename The template location
     * 
     * @return string
     * 
     * @access protected
     */
    protected function loadTemplate($filename)
    {

        // Raise an exception when the file can not be found
        if (!file_exists($filename)) {
            throw new Exception('Template does not exist: ' . $filename);
        }

        // Return the contents of the template
        return file_get_contents($filename);

    }

    /**
     * Load nested templates
     * 
     * @param string $filename The template to check for nested templates
     * 
     * @todo All templates should be in the same directory
     * 
     * @return void
     * 
     * @access protected
     */
    protected function loadNestedTemplates($filename)
    {

        // Check if there are any nested templates
        $matches = array();
        preg_match_all(
            self::TEMPLATE_PATTERN, $this->templates[$filename], $matches
        );

        // Return when no match is found
        if (0 === count($matches[0])) return;

        // Loop through the nested templates
        $templates = $matches[0];

        foreach ($templates as $match) {
            $this->replaceTemplateMatches($match, $filename);
        }

    }

    /**
     * Replace the matches with for the given filename
     * 
     * @param string $match    The matching regex
     * @param string $filename The template where the replacements are done
     * 
     * @return void
     * 
     * @access protected
     */
    protected function replaceTemplateMatches($match, $filename)
    {

        // Get the template path
        $nestedTemplate = substr($match, 6, -1);

        // Load the nested template
        $nestedTemplate = $this->loadTemplate($nestedTemplate);

        // And replace the nested template
        $this->templates[$filename] = preg_replace(
            '/' . $match . '/', $nestedTemplate, $this->templates[$filename]
        );

        $this->templates[$filename];

    }

}