<?php

require_once (dirname(__FILE__) . '/../../TestBootstrap.php');

/**
 * encoding of this file ist important for successful testing
 */
class ZendX_View_AutoescapeTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @var ZendX_View_Autoescape
     */
    protected $_view;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->_view = new ZendX_View_Autoescape();
        $this->_view->setScriptPath(dirname(__FILE__));
    }

    protected function setUp()
    {
        $this->_view->clearVars();
    }

    public function testConstructorDeploysConfigAndInitIteratorFacade()
    {
        $config = array(
            'encoding' => 'UTF-32'
        );
        
        // default escaping
        $view = new ZendX_View_Autoescape();
        $this->assertEquals('UTF-8', $view->getEncoding());
        
        $viewWithConfig = new ZendX_View_Autoescape($config);
        $this->assertEquals('UTF-32', $viewWithConfig->getEncoding());
    }
    
    public function testEmptyViewVarsArray()
    {
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals(array(), $this->_view->getVars());
    }

    /**
     * @see ZendX_View_Facade_StringTest
     */
    public function testHtmlEscapingContextForStringTypes()
    {
        $this->_view->stringValue = 'Hallo <Jens> gib mal €s';
        $this->_view->assign('assignString', '<i>Foo</i>');
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals('Hallo <Jens> gib mal €s', (string) $this->_view->nofilter('stringValue'));
        $this->assertEquals('<i>Foo</i>', (string) $this->_view->nofilter('assignString'));

        $this->assertEquals('Hallo &lt;Jens&gt; gib mal €s', (string) $this->_view->stringValue);
        $this->assertEquals('Hallo &lt;Jens&gt; gib mal €s', (string) $this->_view->html('stringValue'));
        $this->assertEquals('&lt;i&gt;Foo&lt;/i&gt;', (string) $this->_view->assignString);

        $this->assertInstanceOf('ZendX_View_Facade_Null', $this->_view->notExist);
        $this->assertEquals('', (string) $this->_view->notExist);
        $this->assertEquals('', (string) $this->_view->assignString->getProperty('foo'));
    }

    public function testHtmlEscapingContextForBasicDataTypeNumberBoolean()
    {
        $this->_view->numberValue = 123.724;
        $this->_view->BooleanTrue = true;
        $this->_view->BooleanFalse = true;
        $this->_view->assign('assignNr', 234);
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals(123.724, (string) $this->_view->numberValue);
        $this->assertEquals(234, (string) $this->_view->assignNr);
        $this->assertTrue(230 < $this->_view->assignNr);
        $this->assertEquals(235, $this->_view->assignNr + 1);
        $this->assertTrue(true == $this->_view->BooleanTrue);
        $this->assertFalse(false == $this->_view->BooleanFalse);
        $this->assertSame(true, $this->_view->BooleanTrue);
        $this->assertEquals(123.724, $this->_view->html('numberValue'));
        $this->assertEquals(true, $this->_view->html('BooleanTrue'));
        $this->assertEquals(123.724, $this->_view->nofilter('numberValue'));
    }

    /**
     * @see ZendX_View_Facade_NullTest
     */
    public function testHtmlEscapingContextForBasicDataTypeNull()
    {
        $this->_view->nullValue = null;
        $this->_view->render('renderDummy.tpl');

        $this->assertInstanceOf('ZendX_View_Facade_Null', $this->_view->nullValue);
        $this->assertEquals('', (string) $this->_view->html('nullValue'));
    }

    public function testShouldGetFacadeTypingForArrayValues()
    {
        $this->_view->arrayValue = array(
            'erstesElement<br />',
            'deep' => array(
                'magic' => '<tags>'
            ),
            'drittes' => null
        );
        $this->_view->render('renderDummy.tpl');

        $this->assertInstanceOf('ZendX_View_Facade_Iterator', $this->_view->arrayValue);
        $this->assertInstanceOf('ZendX_View_Facade_Iterator', $this->_view->arrayValue->getProperty('deep'));

        $this->assertTrue((boolean) $this->_view->arrayValue);
        $this->assertEquals(3, count($this->_view->arrayValue));
        $this->assertEquals('erstesElement&lt;br /&gt;', (string) $this->_view->arrayValue->getProperty('/0'));
        $this->assertEquals('', (string) $this->_view->arrayValue->getProperty('drittes'));
        $this->assertEquals('', (string) $this->_view->arrayValue->getProperty('drittes/pusteKuchen'));
        $this->assertSame('&lt;tags&gt;', (string) $this->_view->arrayValue->html('deep/magic'));
    }


    public function testShouldGetFacadeTypingForEmptyArray()
    {
        $this->_view->emptyArray = array();
        $this->_view->render('renderDummy.tpl');

        $this->assertSame(0, count($this->_view->emptyArray));
        $this->assertEquals('', (string) $this->_view->emptyArray->getProperty('foo/bar'));
        $this->assertEquals('', (string) $this->_view->emptyArray->html('0'));
    }

    public function testFacadeTypingForComplexTypes()
    {
        $this->_view->customer = new ZendX_Bean(
            array(
                'deepAbstractBean' => new ZendX_Bean(
                    array('hackerz' => '\';alert(String.fromCharCode(88,83,83))//\\\';')
                ),
                'deepStdObject' => (object) array(
                    'hackerz' => '<IMG SRC="javascript:alert(\'XSS\');">'
                )
            )
        );
        $this->_view->render('renderDummy.tpl');

        // string context
        $this->assertEquals('ViewFacade', (string) $this->_view->customer);
        // PHP context 
        $this->assertInstanceOf('ZendX_View_Facade_Bean', $this->_view->customer);
        $this->assertInstanceOf('ZendX_View_Facade_Bean', $this->_view->customer->getProperty('deepAbstractBean'));
        $this->assertInstanceOf('ZendX_View_Facade_Iterator', $this->_view->customer->getProperty('deepStdObject'));
    }

    public function testHtmlEscapingContextForAbstractBeanViaHtmlFunction()
    {
        $this->_view->customer = new ZendX_Bean(// ein AbstractBean Erbe der weichere setProperty() hat
            array(
                'name' => 'Bar<ß>',
                'currency' => '$',
                'deepArray' => array(
                    'internet' => '32<Mbit>',
                    'deepArray' => array(
                        'internet' => '100<Mbit>'
                    )
                ),
                'deepAbstractBean' => new ZendX_Bean(
                        array('hackerz' => 'Schei<ß> Encoding')
                ),
                'deepStdObject' => (object) array(
                    'hackerz' => '<br />'
                ),
                'simpleVar' => 8765
            )
        );
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals('Bar&lt;ß&gt;', (string) $this->_view->html('/customer/name'));
        $this->assertEquals('Bar&lt;ß&gt;', (string) $this->_view->html('//customer/name'));
        $this->assertEquals('', (string) $this->_view->html('//customer/name/'));
        $this->assertEquals('Bar&lt;ß&gt;', (string) $this->_view->html('customer/name'));
        $this->assertEquals('Bar&lt;ß&gt;', (string) $this->_view->customer->html('name'));
        $this->assertEquals('$', (string) $this->_view->customer->html('currency'));
        $this->assertEquals('32&lt;Mbit&gt;', (string) $this->_view->html('customer/deepArray/internet'));
        $this->assertEquals('100&lt;Mbit&gt;', (string) $this->_view->html('customer/deepArray/deepArray/internet'));
        $this->assertEquals('ViewIterator', (string) $this->_view->html('customer/deepArray'));
        $this->assertEquals('ViewFacade', (string) $this->_view->html('customer'));
        $this->assertEquals('Schei&lt;ß&gt; Encoding', (string) $this->_view->customer->html('deepAbstractBean/hackerz'));
        $this->assertEquals('&lt;br /&gt;', (string) $this->_view->customer->html('deepStdObject/hackerz'));

        $this->assertEquals('Bar<ß>', (string) $this->_view->nofilter('customer/name'));
        $this->assertEquals(8765, $this->_view->nofilter('customer/simpleVar'));
    }

    public function testJsonEscapingContextForStringTypes()
    {
        $this->_view->stringValue = 'Hallo <Jens> gib mal €s';
        $this->_view->events = 'function(){$(\'#divId\').html(\'description of € value\');}';
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals('null', $this->_view->json('noViewProperty'));
        $this->assertEquals('"Hallo <Jens> gib mal \u20acs"', $this->_view->json('stringValue'));
        $this->assertEquals('"function(){$(\'#divId\').html(\'description of \u20ac value\');}"', $this->_view->json('events'));
    }

    public function testJsonEscapingContextForBasicDataTypeNumberBooleanNull()
    {
        $this->_view->numberValue = 123.724;
        $this->_view->booleanTrue = true;
        $this->_view->nullValue = null;
        $this->_view->objectType = new ZendX_Bean(array('prop' => 'fettes Value'));
        $this->_view->render('renderDummy.tpl');

        $this->assertSame('123.724', $this->_view->json('numberValue'));
        $this->assertSame('true', $this->_view->json('booleanTrue'));
        $this->assertSame('null', $this->_view->json('nullValue'));
        $this->assertSame('{"_prop":"fettes Value"}', $this->_view->json('objectType'));
    }

    public function testUseEmptyViewVarInArrayContext()
    {
        $this->_view->empty = NULL;
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals('', (string) $this->_view->html('noArray/totallyEmpty'));
        $this->assertEquals('', (string) $this->_view->html('empty/noArray'));
    }
    
    public function testAliasRawDoesTheSameAsNofilter()
    {
        $this->_view->internet = '32<Mbit>';
        $this->_view->render('renderDummy.tpl');

        $this->assertEquals($this->_view->nofilter('internet'), $this->_view->raw('internet'));
        $this->assertEquals($this->_view->nofilter(), $this->_view->raw());
    }
    
    public function testMagicFunctionCallerShouldCallParrentFunction()
    {
        $this->assertNotNull($this->_view->baseUrl());
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error 
     */
    public function testAStrictVarsAndAccessOfInvalidKeyShouldTriggerNotices()
    {
        $this->_view->strictVars();
        
        $this->_view->testing;
    }
    
    public function testDefaultIgnoredDataTypes()
    {
        $this->_view->form = new Zend_Form();
        $this->_view->form->addElement('text', 'testForm');
        $this->_view->navi = new Zend_Navigation();
        $this->_view->paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array(array()));
        
        $this->assertInstanceOf('Zend_Form', $this->_view->form);
        $this->assertInstanceOf('Zend_Form_Element', $this->_view->form->testForm);
        $this->assertInstanceOf('Zend_Navigation', $this->_view->navi);
        $this->assertInstanceOf('Zend_Paginator', $this->_view->paginator);
    }

    public function testReadAndDeployIgnoredDataTypesOnCreation()
    {
        $config = array(
            'ignoredDataTypes' => array(
                'Zend_Captcha_Dumb',
                'ZendX_Bean'
            )
        );
        
        $view = new ZendX_View_Autoescape($config);
        $view->setScriptPath(dirname(__FILE__));
        
        $view->navi = new Zend_Captcha_Dumb();
        $view->bean = new ZendX_Bean();
        $view->render('renderDummy.tpl');
        
        $this->assertInstanceOf('Zend_Captcha_Dumb', $view->navi);
        $this->assertInstanceOf('ZendX_Bean', $view->bean);
    }
    
    public function testDeploymentOfIgnoredDataTypesOnDifferentLevels()
    {
        $config = array(
            'ignoredDataTypes' => array(
                'Zend_Captcha_Dumb',
                'ZendX_Bean'
            )
        );
        
        $view = new ZendX_View_Autoescape($config);
        $view->setScriptPath(dirname(__FILE__));
        
        $content = array(
            'ignoreThis' => array(
                'bean' => new ZendX_Bean()
            )
        );
        
        $view->content = $content;
        $view->render('renderDummy.tpl');
        
        $test = $view->getProperty('content/ignoreThis');
        $this->assertInstanceOf('ZendX_Bean', $test->getProperty('bean'));
    }
    
}
