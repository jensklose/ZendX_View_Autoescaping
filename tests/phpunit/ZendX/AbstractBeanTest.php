<?php

require_once (dirname(__FILE__) . '/../TestBootstrap.php');

/**
 * encoding of this file ist important for successful testing
 */
class ZendX_AbstractBeanTest extends PHPUnit_Framework_TestCase
{

    /** @var AbstractBean */
    protected $testBean;

    protected function setUp()
    {
        $propertyMap = array(
            "foo" => "one",
            "bar" => "two",
            "baz" => new ZendX_TestSubBean(array("abc" => "first", "def" => "second")),
            "quux" => array("ichi", "ni", "san", "shi", new ZendX_TestSubBean(array("abc" => "erster", "def" => "zweiter"))),
            "quuux" => array("null" => "zero", "eins" => "one", "zwei" => "two", "drei" => new ZendX_TestSubBean(array("abc" => "primus", "def" => "secundus")))
        );

        $this->testBean = new ZendX_TestBean($propertyMap);
    }

    public function testBeanSeter()
    {
        $array = array(
            'uno' => 'dos',
            'tres' => 'quadro'
        );
        $bean = new ZendX_TestBean();

        $this->assertTrue($bean->setBean($array));

        $this->assertFalse($bean->setBean('testWithString'));
    }

    public function testHasProperty()
    {
        $this->assertTrue($this->testBean->hasProperty("foo"));

        $this->testBean->setProperty("foo", NULL);
        $this->assertTrue($this->testBean->hasProperty("foo"));

        $this->assertFalse($this->testBean->hasProperty("undefined"));
    }

    public function testGetProperty()
    {
        $this->assertEquals("one", $this->testBean->getProperty("foo"));
        $this->assertEquals("two", $this->testBean->getProperty("bar"));
    }

    public function testSetGetProperty()
    {
        $this->testBean->setProperty("foo", "neuer Wert");
        $this->assertEquals("neuer Wert", $this->testBean->getProperty("foo"));
    }

    public function testNoExceptionWhenObjectWithUndefinedKeyIsCreated()
    {
        $test = new ZendX_TestBean(array("undefined" => "value"));
    }

    public function testNoExceptionWhenUndefinedPropertyIsSet()
    {
        $test = new ZendX_TestBean();

        $test->setProperty("undefined", null);
    }

    public function testReturnOfNullValueWhenUndefinedPropertyIsRead() 
    {
        $test = new ZendX_TestBean();

        $this->assertNull($test->getProperty("undefined"));
    }

    public function testAsArrayWithSubObject() 
    {
        $resultingArray = $this->testBean->asArray();

        $this->assertEquals("one", $resultingArray["foo"]);
        $this->assertEquals("two", $resultingArray["bar"]);
    }

    public function testAsArrayWithSubObjectArray() 
    {
        $resultingArray = $this->testBean->asArray();

        $this->assertEquals("one", $resultingArray["foo"]);
        $this->assertEquals("two", $resultingArray["bar"]);
        $this->assertTrue($resultingArray["baz"] instanceof ZendX_TestSubBean);
        $this->assertTrue($resultingArray["quux"][4] instanceof ZendX_TestSubBean);
        $this->assertTrue($resultingArray["quuux"]["drei"] instanceof ZendX_TestSubBean);
    }

    public function testGetPropertyWithSubObject() 
    {
        $this->assertEquals("one", $this->testBean->getProperty("foo"));
        $this->assertEquals("first", $this->testBean->getProperty("baz/abc"));
        $this->assertEquals("ichi", $this->testBean->getProperty("quux/0"));
        $this->assertEquals("erster", $this->testBean->getProperty("quux/4/abc"));
        $this->assertEquals("zero", $this->testBean->getProperty("quuux/null"));
        $this->assertEquals("primus", $this->testBean->getProperty("quuux/drei/abc"));
    }

    public function testGetPropertyWithSubObjectAsDeepArray() 
    {
        $resultingArray = $this->testBean->asDeepArray();

        $this->assertEquals('one', $resultingArray['foo']);
        $this->assertEquals('first', $resultingArray['baz']['abc']);
        $this->assertEquals('ichi', $resultingArray['quux'][0]);
        $this->assertEquals('erster', $resultingArray['quux'][4]['abc']);
        $this->assertEquals('zero', $resultingArray['quuux']['null']);
        $this->assertEquals('primus', $resultingArray['quuux']['drei']['abc']);
    }

    public function testGetPropertyWithNullProperty() 
    {
        $testBean = new ZendX_TestBean(array('one' => array('two' => NULL)));

        $this->assertNull($testBean->getProperty('one/two/three/four'));
    }

    public function testAdditionOfArrayProperties() 
    {
        $this->testBean->setProperty("quuux/test", "neuer Wert");
        $this->assertEquals("neuer Wert", $this->testBean->getProperty("quuux/test"));

        $this->testBean->setProperty("quuux/deepTest/one/two/three/test", "zweiter neuer Wert");
        $this->assertEquals("zweiter neuer Wert", $this->testBean->getProperty("quuux/deepTest/one/two/three/test"));
        
        $this->testBean->setProperty("quuux/7", "dritter neuer Wert");
        $this->assertEquals("dritter neuer Wert", $this->testBean->getProperty("quuux/7"));
    }
    
    public function testSetValueWithIndizeInEmptyBean()
    {
        $this->testBean = new ZendX_TestBean();
        
        $this->testBean->setProperty("quuux/0", "modern walking");
        $this->assertEquals("modern walking", $this->testBean->getProperty("quuux/0"));
        
        $this->testBean->setProperty("quuux/test1", "ganz neuer Wert");
        $this->assertEquals("ganz neuer Wert", $this->testBean->getProperty("quuux/test1"));
        
        $this->testBean->setProperty("quuuux/1/3", "triplet");
        $this->assertEquals("triplet", $this->testBean->getProperty("quuuux/1/3"));
    }
    
    public function testSetValueWithIndizeInEmptyObject()
    {
        $this->testBean = new ZendX_TestBean();
        
        $this->testBean->setProperty("quuux/0", new stdClass());
        $this->assertEquals(new stdClass(), $this->testBean->getProperty("quuux/0"));
        
        $this->testBean->setProperty("quuux/0/1", "trials");
        $this->assertEquals("trials", $this->testBean->getProperty("quuux/0/1"));
        
        $this->testBean->setProperty("quuux/0/2/3", "memorizing");
        $this->assertEquals("memorizing", $this->testBean->getProperty("quuux/0/2/3"));
    }
    
    public function testAdditionOfStdObjectProperties() 
    {
        $array = array(
            "alpha" => array("eins", "zwei", "drei"),
            "beta" => new ZendX_TestBean());

        $object = (object) $array;

        $this->testBean->setProperty("quuux/test", $object);
        $this->testBean->setProperty("quuux/test/beta/foo", "foobar");
        $this->assertEquals("eins", $this->testBean->getProperty("quuux/test/alpha/0"));
        $this->assertEquals("foobar", $this->testBean->getProperty("quuux/test/beta/foo"));
    }

    public function testGetPropertyWithEmptyPathComponent() 
    {
        $this->assertNull($this->testBean->getProperty("quux/"));
    }

    public function testAccessingPropertyWithMultipleSlashesAtTheBeginning() 
    {
        $array = array(
            "alpha" => "omega"
        );

        $bean = new ZendX_TestBean($array);

        $this->assertNULL($bean->getProperty('////alpha'));
    }

    public function testGetPropertyWithSlashAtStart() 
    {
        $this->assertEquals("one", $this->testBean->getProperty('/foo'));
    }

    public function testSetPropertyWithGenericObject() 
    {
        $array = array(
            'ichi' => new stdClass()
        );
        $bean = new ZendX_TestBean($array);

        $bean->setProperty('/ichi/ni', 'sans');
        $this->assertEquals('sans', $bean->getProperty('ichi/ni'));
    }

    /**
     * usage of Zend_Config in the view is forbidden
     * 
     * @expectedException ErrorException
     */
    public function testUsageWithZendConfigTriggersException() 
    {
        $array = array(
            "alpha" => array("eins", "zwei", "drei"),
            "beta" => new ZendX_TestBean()
        );
        $config = new Zend_Config($array, true);
        
        $this->testBean->setProperty('Zend/Config', $config);
        $this->assertInstanceOf('Zend_Config', $this->testBean->getProperty('Zend/Config'));

        $this->assertEquals('eins', $this->testBean->getProperty('Zend/Config/alpha/0'));
        $this->testBean->setProperty('Zend/Config/alpha/0', 'zehn');
        $this->assertEquals('zehn', $this->testBean->getProperty('Zend/Config/alpha/0'));
    }

}
