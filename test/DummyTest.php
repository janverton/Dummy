<?php

// Include the dummy class
require_once dirname(__FILE__) . '/../Dummy.php';

/**
 * Test class for Dummy.
 */
class DummyTest extends PHPUnit_Framework_TestCase
{

    /**
     * A Dummy instance.
     * 
     * @var \STD\Dummy
     */
    protected $dummy;

    /**
     * Set up the environment for testing the Dummy class
     */
    protected function setUp()
    {
        
        // Create a new dummy instance
        $this->dummy = new \SDT\Dummy;

    }

    /**
     * Get a template and make sure the content is ok
     */
    public function testGetTemplate()
    {

        // Load the template and get it's contents
        $content = $this->dummy->getTemplate('getTemplate.tpl');

        // Make sure the content equals out dummyTest template
        $this->assertEquals('dummyTest', $content);

    }

    /**
     * Get a not existing template
     */
    public function testGetNotExistingTemplate()
    {

        // An exception should be thrown
        $this->setExpectedException('Exception');

        // Load the not existing template
        $this->dummy->parseTemplate('bla.tpl');

    }

    /**
     * Replace a variable in a template
     */
    public function testReplaceVariable()
    {

        // Get the template
        $this->dummy->parseTemplate('replaceVar.tpl');

        // Replace the value
        $this->dummy->replace('name', 'World');

        // Check the content of the template output
        $content = $this->dummy->getTemplate('replaceVar.tpl');
        $this->assertEquals(
            'Hello World!', $content,
            'Template output does not match with rendered output'
        );

    }

    /**
     * Nest a template in another template
     */
    public function testTemplateNesting()
    {

        // Get the template
        $content = $this->dummy->getTemplate('parent.tpl');

        // The parent contains Hello, the child contains Mother
        $this->assertEquals(
            'Hello Mother!', $content, 'Child template not loaded'
        );

    }

    /**
     * Nest 2 templates in a template
     */
    public function testMultipleTemplateNesting()
    {

        // Test multiple templates in one template
        $content = $this->dummy->getTemplate('parent2.tpl');

        // Child 1 contains Hello, child 2 contains Children!
        $this->assertEquals(
            'Hello Children!', $content, 'Children templates not loaded'
        );

    }

    /**
     * Nest 2 of the same templates in a template
     */
    public function testMultipleTemplateNestingWithOneTemplate()
    {

        // Test multiple templates in one template
        $content = $this->dummy->getTemplate('parent3.tpl');

        // Child 1 contains Hello, and it will be loaded twice
        $this->assertEquals(
            'Hello Hello!', $content, 'Children templates not loaded'
        );

    }

    /**
     * A template in a template in a template.. like inception, right?
     */
    public function testDepthTemplateNesting()
    {

        // Load a template that loads a template that loads a template, which
        // eventually says Hello Mother!
        $content = $this->dummy->getTemplate('inception.tpl');

        // Make sure it says hello
        $this->assertEquals(
            'Hello Mother!', $content,
            'Inception templates did not load correctly'
        );

    }

    /**
     * Test a loop 
     */
    public function testLoop()
    {

        // Get a template with a loop
        $this->dummy->parseTemplate('loop.tpl');

        // Create an array with stdClass objects with a name and age property 
        // assigned
        $vocals = new stdClass;
        $vocals->name = 'Freddie';
        $vocals->age = 65;

        $guitars = new stdClass;
        $guitars->name = 'Brian';
        $guitars->age = 66;
        
        // Add the objects to a data array
        $data = array($vocals, $guitars);

        // Now assign data to the loop
        $this->dummy->assignLoop('artists', $data);

        // Check if the template and loop have been rendered well
        $content = $this->dummy->getTemplate('loop.tpl');
        $this->assertEquals(
            'Name: Freddie (65) Name: Brian (66) ', $content, 
            'Loop did not parse succesfully'
        );

    }
    
    /**
     * Test a not existing loop 
     */
    public function testNotExistingLoop()
    {
        
        // Get a template with a loop
        $this->dummy->parseTemplate('loop.tpl');
        
        // Now assign data to a not existing loop
        $this->assertFalse(
            $this->dummy->assignLoop('foo', array()),
            'Loop found while it does not exist'
        );
        
    }
    
    /**
     * A custom template directory can be set
     */
    public function testSetTemplateDirectory()
    {
        
        // Set a custom template directory
        $this->assertTrue(
            $this->dummy->setTemplateDirectory('test/subdir'),
            'Template directory could not be set'
        );
        
    }
    
    /**
     * An invalid template directory raises an exception
     */
    public function testSetInvalidTemplateDirectory()
    {
        
        // An exception should be thrown
        $this->setExpectedException('Exception');
        
        // Boom does not exist
        $this->dummy->setTemplateDirectory('boom');
        
    }
    
    /**
     * Retrieve a template from a set template directory
     */
    public function testGetTemplateFromTemplateDirectory()
    {
        
        // Set a custom template directory
        $this->assertTrue(
            $this->dummy->setTemplateDirectory('test/subdir'),
            'Template directory could not be set'
        );
        
        // Make sure something is returned
        $this->assertNotNull(
            $this->dummy->getTemplate('setTemplateDir.tpl'), 
            'Template could not be retrieved'
        );
        
    }
    
    /**
     * Retrieve a template from relative to the set template directory
     */
    public function testGetTemplateFromRelativeDirectory()
    {
        
        // Set a custom template directory
        $this->assertTrue(
            $this->dummy->setTemplateDirectory('test/subdir'),
            'Template directory could not be set'
        );
        
        // Make sure something is returned
        $this->assertNotNull(
            $this->dummy->getTemplate('../child.tpl'), 
            'Template could not be retrieved'
        );
        
    }
    
    /**
     * Retrieve a relative template from the set template directory
     */
    public function testRelativeNestedTemplate()
    {
        
        // Set a custom template directory
        $this->assertTrue(
            $this->dummy->setTemplateDirectory('test/subdir'),
            'Template directory could not be set'
        );
        
        // Make sure something is returned
        $this->assertEquals(
            'Mother',
            $this->dummy->getTemplate('relativeTemplate.tpl'), 
            'Template did not parse correct'
        );
        
    }
    
}