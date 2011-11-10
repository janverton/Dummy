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
     * Pattern where looping is matched against
     * 
     * @access public
     */
    const LOOP_PATTERN = '/{loop [a-zA-Z0-9_]+}(.*?){\/loop}/';
    
    /**
     * Pattern where loop variables are matched against
     * 
     * @access public
     */
    const LOOP_VARIABLE_PATTERN = '/{:[a-zA-Z0-9_]+}/';
    
    /**
     * Loaded templates
     * 
     * @var array
     * @access protected
     */
    protected $templates = array();
    
    /**
     * The parsed template where variables and loops are matched against
     * 
     * @var String
     * @access protected
     */
    protected $parsedTemplate = null;
    
    /**
     * Loops available in this template
     * 
     * @var array
     * @access protected
     */
    protected $loops = null;
    
    /**
     * Parse the given template. This means nested templates will be included 
     * and parsed as well. Note that this needs to be done before any values or
     * loops can be assigned.
     * 
     * @param String $filename Location of the template file
     * 
     * @example $dummy->parseTemplate('foo.tpl');
     * 
     * @return void
     * 
     * @access public 
     */
    public function parseTemplate($filename)
    {
        
        // Return when the template is already parsed
        if (isset($this->parsedTemplate)) {
            return;
        }
        
        // Load the template contents 
        $this->templates[$filename] = $this->loadTemplate($filename);

        // Get any nested templates while available
        do {
            $hasNestedTemplates = $this->loadNestedTemplates($filename); 
        } while ($hasNestedTemplates);

        
        // Parse the template and get the contents
        $this->parsedTemplate = $this->templates[$filename];
        
    }
    
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
        
        // When the template is not parsed yet.. do it!
        if (!isset($this->parsedTemplate)) {
            $this->parseTemplate($filename);
        }

        // Return the template
        return $this->parsedTemplate;

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
     * @return void
     * 
     * @access public
     */
    public function replace($name, $value)
    {

        // The variable name to replace
        $variableName = '{' . $name . '}';

        // Replace the variable with the give value
        $this->parsedTemplate = str_replace(
            $variableName, $value, $this->parsedTemplate
        );

        // Return
        return;

    }
    
    /**
     * Replace a loop with the given data set. This needs to be an array 
     * containing objects of type stdClass
     * 
     * @param String $loopName Name of the loop to use
     * @param array  $data     Data to populate
     * 
     * @example assignLoop('foo', array(stdClass))
     * 
     * @return boolean
     * 
     * @access public 
     */
    public function assignLoop($loopName, array $data)
    {
        
        // Get the available loops
        $loops = $this->getLoops();
        
        // Make sure the loop exists
        if (!isset($loops[$loopName])) {
            return false;
        }
        
        // Get the content pattern of the loop
        $loopContentPattern = $this->getLoopContentPattern($loops[$loopName]);
        
        // Parse the loop with the given data set
        $parsedContent = $this->parseLoop($loopContentPattern, $data);
        
        // Replace loop patern with the parsed loop content
        $this->parsedTemplate = str_replace(
            $loops[$loopName], $parsedContent, $this->parsedTemplate
        );
        
        // When we've come this far all should be okay
        return true;
    }
    
    /**
     * Load the template file contents
     * 
     * @param String $filename The template location
     * 
     * @return String
     * 
     * @throws Exception
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
     * @todo All templates are loaded from the current directory, a custom 
     * configurable template directory would be nicer.
     * 
     * @return boolean Returns true when a nested template is loade or false 
     *   otherwise
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
        if (0 === count($matches[0])) {
            return false;
        }

        // Loop through the nested templates
        $templates = $matches[0];

        foreach ($templates as $match) {
            $this->replaceTemplateMatches($match, $filename);
        }

        // Nested templates loaded, return true
        return true;

    }

    /**
     * Replace the template contents with the template pattern match for the
     * given template
     * 
     * @param string $match    The template regex match
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
    
    /**
     * Get all the loops from the parsed template
     * 
     * @return array An array with the available loops with their name as key
     * 
     * @access protected
     */
    protected function getLoops()
    {
        
        // When not set, extract the loops from the parsed template
        if (!isset($this->loops)) {
            
            // Extract all available loops
            preg_match_all(self::LOOP_PATTERN, $this->parsedTemplate, $loops);
            
            // Only use the full matches
            $loops = $loops[0];
            
            // Order them by name
            $this->loops = $this->orderLoopsByName($loops);
            
        }
        
        // Return the loops
        return $this->loops;
        
    }
    
    /**
     * Order an array containing loops extracted from the template
     * 
     * @param array $loops The loop patterns to order by name
     * 
     * @example orderLoopsByName(array(''{loop foo}{:bar}{/loop}''))
     *   => array('foo' => '{loop foo}{:bar}{/loop}')
     * 
     * @return array The given loops order by their name
     * 
     * @access protected
     */
    protected function orderLoopsByName(array $loops)
    {
        
        // Contains the ordered loops
        $orderedLoops = array();
        
        // Order the loops by their name
        foreach ($loops as $loopMatches) {

            // The longest loop match
            $loop = $loopMatches;

            // Extract the loop name. The name starts at position 6 and ends
            // by the first '}' for the matching loop
            $loopNameLength = stripos($loop, '}') - 6;
            $key = substr($loop, 6, $loopNameLength);
            
            // Add the loop with the corresponding key
            $orderedLoops[$key] = $loop;

        }
        
        // Return the ordered loops
        return $orderedLoops;
    }
    
    /**
     * Filters the content pattern of the loop and returns it.
     * 
     * @param String $loop The loop pattern extracted from the template
     * 
     * @example getLoopContents('{loop foo}{:bar}{/loop}')
     *   => '{:bar}'
     * 
     * @return String The content pattern of the loop
     * 
     * @access protected
     */
    protected function getLoopContentPattern($loop)
    {
        
        // Get the starting point (after the first '}')
        $contentStart = stripos($loop, '}') + 1;
        
        // Get the content length (before the last occuring '{')
        $contentLength = strripos($loop, '{') - $contentStart;
        
        // Get the contents of the loop
        $content = substr($loop, $contentStart, $contentLength);
        
        // Return
        return $content;
        
    }
    
    /**
     * Extract the object variables needed by the loop pattern and return them
     * as an array
     * 
     * @param type $loopPattern The pattern to get the variable names from
     * 
     * @example getVariableNamesFromLoop('{:foo} bla {:bar}')
     *   => array('foo', 'bar')
     * 
     * @return array
     * 
     * @access protected
     */
    protected function getVariableNamesFromLoop($loopPattern)
    {
        
        // Get the needed vars from the loopcontent
        preg_match_all(self::LOOP_VARIABLE_PATTERN, $loopPattern, $matches);
        
        // Only use exact matches
        $variables = $matches[0];
        
        // Strip the pattern and keep the variable name
        $names = array();
        foreach ($variables as $variable) {
            $names[] = substr($variable, 2, -1);
        }
        
        // Return
        return $names;
        
    }
    
    /**
     * Populate a loop with the given data set. This needs to be an array 
     * containing objects of type stdClass
     * 
     * @param String $loopPattern The pattern to parse
     * @param array  $data        Data to populate the pattern with
     * 
     * @example $object = new stdClass; 
     *   $object->foo = 'bar';
     *   parseLoop('bar{:foo}baz', array($object));
     *   => 'barbarbaz'
     * 
     * @return String The parsed content
     * 
     * @access protected
     */
    protected function parseLoop($loopPattern, array $data)
    {
        
        // Extract the var names from the loop content
        $objectVars = $this->getVariableNamesFromLoop($loopPattern);
        
        // Get the size of the dataset
        $numberOfLoops = count($data);
        
        // Replace the data for each loop and add it to the parsed content
        $parsedContent = '';
        for ($i = 0; $i < $numberOfLoops; $i++) {
            
            // Get a clean loop content pattern
            $parsedLoopContent = $loopPattern;
            
            // Replace all variables for this loop with the right content
            foreach ($objectVars as $varName) {
                
                // When the object does not have a property skip the replacement
                if (!property_exists($data[$i], $varName)) {
                    continue;
                }
                
                // Replace the template variable with the according property
                // of the current object
                $parsedLoopContent = str_replace(
                    '{:' . $varName . '}',
                    $data[$i]->$varName, 
                    $parsedLoopContent
                );
            }
            
            // Add the created loop to the parsed content
            $parsedContent .= $parsedLoopContent;
        }
        
        // Return the created content
        return $parsedContent;
        
    }
    
}