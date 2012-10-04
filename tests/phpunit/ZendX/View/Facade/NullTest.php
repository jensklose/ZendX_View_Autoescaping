<?php

require_once (dirname(__FILE__) . '/../../../TestBootstrap.php');

class ZendX_View_Facade_NullTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_View
     */
    protected $_view;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->_view = new ZendX_View_Autoescape();
    }

    protected function setUp()
    {
        $this->_view->clearVars();
    }
    
    /**
     * encoding of this file ist important for successful testing
     */
    public function testShouldGetEscapedValuesForContext()
    {
        $testObject = new ZendX_View_Facade_Null('Hallo &lt;Jens&gt; gib mal €s', $this->_view);
        
        $this->assertEquals('', (string) $testObject);
        $this->assertInstanceOf('ZendX_View_Facade_Null', $testObject->html());
        $this->assertEquals('', (string) $testObject->nofilter());
        $this->assertEquals('', (string) $testObject->raw());
        $this->assertEquals('', (string) $testObject->html());
        $this->assertEquals('', (string) $testObject->json());
        $this->assertEquals('', (string) $testObject->urlencode());
    }
    
    public function testValidShouldReturnFalse()
    {
        $count = array(
            'name' => 'Foo<ß>',
        );
        $test = new ZendX_View_Facade_Null($count, $this->_view);
        
        $this->assertFalse($test->valid());
    }

    public function testSetPropertyShouldDoNothing()
    {
        $test = new ZendX_View_Facade_Null('Foo<ß>', $this->_view);
        
        $test->setProperty('name', 'encoding');
        
        $this->assertEquals('', (string) $test->getProperty('name'));
    }
    
    public function testDisabledArrayFunctions()
    {
        $array = array(
            'names' => array(
                'uno',
                'dos',
                'tres'
            )
        );
        
        $test = new ZendX_View_Facade_Null($array, $this->_view);

        $this->assertEquals(0, $test->count());
        $this->assertEquals('', $test->current());
        $this->assertEquals(NULL, $test->key());
        
        foreach($test->getProperty('names') as $value) {
            trigger_error('Facade_Null shouldnt have Values', E_USER_NOTICE);
        }
    }
}
