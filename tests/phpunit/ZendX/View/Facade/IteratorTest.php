<?php

require_once (dirname(__FILE__) . '/../../../TestBootstrap.php');

/**
 * Test class for ZendX_View_BeanIterator.
 */
class ZendX_View_Facade_IteratorTest extends PHPUnit_Framework_TestCase
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
    
    public function testEmptyArray()
    {
        $test = new ZendX_View_Facade_Iterator(array(), $this->_view);
        
        $this->assertEquals('', (string) $test->getProperty('0'));
        $test->rewind();
    }

    public function testEscapingForGetProperty()
    {
        $test = new ZendX_View_Facade_Iterator(
            array(
                'name' => 'Foo<ß>',
                'currency' => '$',
                'deepArray' => array(
                    'internet' => '32<Mbit>'
                ),
                'deepAbstractBean' => new ZendX_Bean(
                    array('ortsteil' => 'Scheiß Encoding')
                ),
                'deepStdObject' => (object) array(
                    'hackerz' => 'simple<strong>'
                ),
                'numberVar' => 4532.13,
                'nullVar' => null
            ),
            $this->_view
        );

        $this->assertEquals('Foo&lt;ß&gt;', (string) $test->getProperty('name'));
        $this->assertEquals('$', (string) $test->getProperty('//currency'));
        $this->assertEquals(4532.13, $test->getProperty('numberVar'));
        $this->assertEquals('32&lt;Mbit&gt;', (string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('Scheiß Encoding', (string) $test->getProperty('deepAbstractBean/ortsteil'));
        $this->assertEquals('simple&lt;strong&gt;', (string) $test->getProperty('deepStdObject/hackerz'));
        $this->assertEquals('', (string) $test->getProperty('nullVar'));
    }

    public function testHtmlEscapingContextForArrayRequest()
    {
        $test = new ZendX_View_Facade_Iterator(
            array(
                'name' => 'Foo<ß>',
                'currency' => '$',
                'deepArray' => array(
                    'internet' => '32<Mbit>'
                ),
                'nullValue' => null,
                'deepAbstractBean' => new ZendX_Bean(
                    array('ortsteil' => 'Scheiß Encoding')
                ),
                'deepStdObject' => (object) array(
                    'hackerz' => 'simple<strong>'
                )
            ),
            $this->_view
        );

        $this->assertEquals(6, count($test)); // Countable interface
        $test->rewind();
        $this->assertTrue($test->valid());
        $this->assertEquals('name',$test->key());
        $this->assertEquals('Foo&lt;ß&gt;',(string) $test->current());
        $test->next();
        $this->assertTrue($test->valid());
        $this->assertEquals('$',(string) $test->current());
        $test->next();
        $this->assertTrue($test->valid());
        $this->assertEquals('ViewIterator',(string)$test->current());
        $this->assertEquals('32&lt;Mbit&gt;',(string) $test->current()->html('internet'));
        $test->next();
        $this->assertTrue($test->valid());
        $this->assertEquals('',(string) $test->current());
        $test->next();
        $this->assertTrue($test->valid());
        $this->assertEquals('Scheiß Encoding',(string) $test->current()->getProperty('ortsteil'));
        $test->next();
        $this->assertTrue($test->valid());
        $this->assertInstanceOf('ZendX_View_Facade_Iterator', $test->current());
        $this->assertEquals('simple&lt;strong&gt;', (string)$test->current()->getProperty('hackerz'));
        $test->next();
        $this->assertFalse($test->valid());
        $this->assertEquals('', (string) $test->key());
        $this->assertEquals('', (string) $test->current());
        $test->next();
        $this->assertFalse($test->valid());
        
    }

    public function testEscapingContextSwitch()
    {
        $test = new ZendX_View_Facade_Iterator( 
            array(
                'deepArray' => array(
                    'internet' => '32<Mbit>'
                ),
            ),
            $this->_view
        );
        
        $this->assertEquals('32<Mbit>',(string) $test->getProperty('deepArray/internet', 'nofilter'));
        $this->assertEquals('32<Mbit>',(string) $test->getProperty('deepArray/internet', 'raw'));
        $this->assertEquals('32&lt;Mbit&gt;',(string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('32&lt;Mbit&gt;',(string) $test->getProperty('deepArray/internet'));
        $this->assertEquals('32&lt;Mbit&gt;',(string) $test->html('deepArray')->html('internet'));
    }

    public function testHtmlEscapingArrayKey()
    {
        $test = new ZendX_View_Facade_Iterator( 
            array(
                '<es>caped</Index>' => 'don\'t forget <the> index key'
            ),
            $this->_view
        );

        foreach ($test as $key => $value) {
            $this->assertEquals('&lt;es&gt;caped&lt;/Index&gt;', (string) $key); 
        }

        foreach ($test->nofilter() as $key => $value) {
            $this->assertEquals('<es>caped</Index>', $key);
            $this->assertEquals('', (string) $value, 'Rekursiver Zugriff auf tiefe Datenstrukturen mit dem "/" wurde nicht aufgeloest.');
        }
        
        foreach ($test->raw() as $key => $value) {
            $this->assertEquals('<es>caped</Index>', $key);
            $this->assertEquals('', (string) $value, 'Rekursiver Zugriff auf tiefe Datenstrukturen mit dem "/" wurde nicht aufgeloest.');
        }        
        
        foreach ($test as $key => $value) {
            $this->assertEquals('<es>caped</Index>', $key); 
        }
    }

    public function testHtmlEscapingArrayKeyAndValues()
    {
        $test = new ZendX_View_Facade_Iterator( 
            array(
                '<es>caped<script>' => 'don\'t forget <the> index key'
            ),
            $this->_view
        );

        foreach ($test as $key => $value) {
            $this->assertEquals('&lt;es&gt;caped&lt;script&gt;', (string) $key); 
        }

        foreach ($test->nofilter() as $key => $value) {
            $this->assertEquals('<es>caped<script>', $key);
            $this->assertEquals('don\'t forget <the> index key', (string) $value);
        }
        
        foreach ($test->raw() as $key => $value) {
            $this->assertEquals('<es>caped<script>', $key);
            $this->assertEquals('don\'t forget <the> index key', (string) $value);
        }
        
        foreach ($test->html() as $key => $value) {
            $this->assertEquals('&lt;es&gt;caped&lt;script&gt;', $key);
            $this->assertEquals('don\'t forget &lt;the&gt; index key', (string) $value);
        }
    }
}