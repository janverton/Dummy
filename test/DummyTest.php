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
     * @var Dummy
     */
    protected $dummy;

    /**
     * Set up the environment for testing the Dummy class
     */
    protected function setUp()
    {
        $this->dummy = new Dummy;

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

        // An error should be thrown
        $this->setExpectedException('Exception');

        // Load the not existing template
        $this->dummy->getTemplate('bla.tpl');

    }

    /**
     * Replace a variable in a template
     */
    public function testReplaceVariable()
    {

        // Get the template
        $this->dummy->getTemplate('replaceVar.tpl');

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

}