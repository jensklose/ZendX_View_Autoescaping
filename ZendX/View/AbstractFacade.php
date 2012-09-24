<?php
/**
 * Zend Framework Extention
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category    ZendX
 * @package     ZendX_View_Autoescape
 * @subpackage  View
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 * @author      Jens Klose <jens.klose@gmail.com>
 * @copyright   2011 Jens Klose <jens.klose@gmail.com>
 */

/**
 * Covers the view variable calls to complex types
 * 
 * @package     ZendX_View_Autoescape
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class ZendX_View_AbstractFacade
{
    /**
     * @var Zend_View 
     */
    protected $_view;
    
    /**
     * @var string
     */
    protected $_escapingContext = 'html';
    
    /**
     * default _ignoredDataTypes
     * 
     * @var array
     */
    protected $_ignoredDataTypes = array(
        'ZendX_View_AbstractFacade',
        'Zend_Form_Element',
        'Zend_Form',
        'Zend_Navigation',
        'Zend_Paginator'
    );
    
    /**
     * Constructor: deploys given variables
     * 
     * @param array $data
     * @param Zend_View $view
     * @param string $context 
     */
    public function __construct($data, Zend_View $view, $context='html')
    {
        $this->_view = $view;
        $this->_escapingContext = $context;
        $this->_initData($data);
    }
    
    abstract protected function _initData($data);
    
    /**
     * Covers using in string context
     *
     * @return string
     */
    abstract public function __toString();
    
    /**
     * Fetch the unescaped value for given key 
     * 
     * Allows complex requests with syntactic keys
     * 
     * @param string $key 
     * @return string
     */
    abstract protected function _fetchRawValue($key);
    
    /**
     * special escaping
     * 
     * @param string $raw
     * @param string $context
     * @return string 
     */
    protected function _escapeForContext($raw, $context)
    {
        switch ($context) {
            case 'nofilter':
            case 'raw':
                return (string) $raw;
                break;
            case 'url':
                return urlencode((string) $raw);
                break;
            case 'json':
                if (is_string($raw)) {
                    return
                    Zend_Json::encode(
                        iconv($this->_view->getEncoding(), 'UTF-8', $raw),
                        true
                    );
                } else {
                    return Zend_Json::encode($raw, true);
                }
                break;

            default:
                return $this->_view->escape((string) $raw);
                break;
        }
    }

    /**
     * simple geter for _ignoredDataTypes
     * 
     * @return array
     */
    public function getIgnoredDataTypes()
    {
        return $this->_ignoredDataTypes;
    }
    
    /**
     * Add one or more ignoredDataTypes to the stack.
     *
     * @param string|array One or more DataTypes to add.
     */
    public function addIgnoredDataTypes($dataTypes)
    {
        foreach ((array) $dataTypes as $value) {
            $this->_ignoredDataTypes[] = $value;
        }
    }
    
    /**
     * Resets the ignoredDataTypes stack.
     * 
     * @param string|array One or more DataTypes to add.
     */
    public function setIgnoredDataTypes($dataTypes)
    {
        $this->_ignoredDataTypes = array();
        $this->addIgnoredDataTypes($dataTypes);
    }
    
    /**
     * universal interface method with context switch or standard html escaping
     *
     * @param string $key
     * @param string $context   the escaping context like
     * - html
     * - nofilter
     * - json
     */
    public function getProperty($key, $context = null)
    {
        $raw = $this->_fetchRawValue(ltrim($key, '/'));
        if ($this->_isIgnoredDataType($raw)) {
            return $raw;
        }
        if (is_null($context)) {
            $context = $this->_escapingContext;
        }
        switch (gettype($raw)) {
            case 'string':
                return new ZendX_View_Facade_String($raw, $this->_view, $context);//$this->_escapeForContext($raw, $context);
                break;
            case 'object':
                if ($raw instanceof ZendX_AbstractBean) {
                    $property = new ZendX_View_Facade_Bean($raw, $this->_view, $context);
                } else {
                    $property = new ZendX_View_Facade_Iterator((array) $raw, $this->_view, $context);
                }
                
                // deploy ignoredDataTypes
                $property->setIgnoredDataTypes($this->getIgnoredDataTypes());
                return $property;
                break;
            case 'array':
                $property = new ZendX_View_Facade_Iterator($raw, $this->_view, $context);
                
                // deploy ignoredDataTypes
                $property->setIgnoredDataTypes($this->getIgnoredDataTypes());
                return $property;
                break;
            case 'boolean':
            case 'integer':
            case 'double':
                return $raw;
                break;
            case 'NULL':
            case 'unknown type':
            case 'resource':
            default :
                return new ZendX_View_Facade_Null('', $this->_view, $context);
                break;
                
        }
        return new ZendX_View_Facade_String('', $this->_view, $context);
    }

    /**
     * html facading
     * 
     * @return mixed
     */
    public function html()
    {
        if (func_num_args()==0) {
            $this->_escapingContext = 'html';
            return $this;
        } else {
            $key = func_get_arg(0);
        }
        return $this->getProperty($key, 'html');
    }

    /**
     * url facading
     * 
     * @return mixed
     */
    public function url()
    {
        if (func_num_args()==0) {
            $this->_escapingContext = 'url';
            return $this;
        } else {
            $key = func_get_arg(0);
        }
        return $this->getProperty($key, 'url');
    }

    /**
     * nofilter facading
     * 
     * @return mixed
     */
    public function nofilter()
    {
        if (func_num_args()==0) {
            $this->_escapingContext = 'nofilter';
            return $this;
        } 
        return $this->getProperty(func_get_arg(0), 'nofilter');
    }
    
    /**
     * raw alias for nonfilter facading
     * 
     * @return mixed
     */
    public function raw()
    {
        if (func_num_args()==0) {
            return $this->nofilter();
        } 
        return $this->nofilter(func_get_arg(0));
    }

    /**
     * json facading
     * 
     * @return mixed
     */
    public function json()
    {
        if (func_num_args()==0) {
            $this->_escapingContext = 'json';
            return $this;
        } else {
            $key = func_get_arg(0);
            $raw = $this->_fetchRawValue(ltrim($key, '/'));
        }
        return $this->_escapeForContext($raw, 'json');
    }
    
    /**
     * checks if given property shall be ignored when output
     * 
     * @param mixed $check
     * @return boolean
     */
    protected function _isIgnoredDataType($check)
    {
        foreach($this->getIgnoredDataTypes() as $class) {
            if($check instanceof $class)
                return true;
        }
        
        return false;
    }
    
}
