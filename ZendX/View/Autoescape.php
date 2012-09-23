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
 * Alternative view renderer with autoescaping 
 * 
 * @package     ZendX_View_Autoescape
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_View_Autoescape extends Zend_View
{
    /**
     * contains the raw view variables
     * 
     * @var mixed
     */
    private $_raw = array();
    
    /**
     * facade for proper view var access
     * 
     * @var ZendX_View_Facade_Iterator
     */
    private $_facadedVars = null;
    
    /**
     * enables notices when view var does not exist
     * 
     * @var boolean
     */
    private $_strictVarsAutoescape = false;

    
    /**
     * Constructor: adds Zend_View config and creates facade object
     * 
     * @param array $config 
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_facadedVars = new ZendX_View_Facade_Iterator(null, $this);
        
        // add user-defined Whitelist for object usages
        if(array_key_exists('ignoredDataTypes', $config)) {
            $this->_facadedVars->addIgnoredDataTypes($config['ignoredDataTypes']);
        }
        
    }

    /**
     * Overrides method from Zend_View
     */
    protected function _run()
    {
        $this->_updateVars();
        parent::_run(func_get_arg(0));
    }

    /**
     * Overrides method from Zend_View
     */
    public function __get($key)
    {
        $this->_updateVars();
        if ($this->_strictVarsAutoescape) {
            if (!isset($this->_raw[$key])) {
                trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
            }
        }
        return $this->_facadedVars->getProperty($key);
    }

    /**
     * view var deployment to raw structure and facade object 
     */
    private function _updateVars()
    {
        foreach (parent::getVars() as $key => $value) {
            $this->_raw[$key] = $value;
            $this->_facadedVars->setProperty($key, $value);
        }
        $this->clearVars();
    }

    /**
     * Returns all assigned vars in original context
     *
     * @return array
     */
    public function getVars()
    {
        $this->_updateVars();
        return $this->_raw;
    }

    /**
     * Overrides method from Zend_View
     * 
     * @return mixed    Facades or allowed types
     */
    public function __call($name, $args)
    {
        if (method_exists($this->_facadedVars, $name)) {
            return call_user_func_array(array($this->_facadedVars, $name), $args);
        } else {
            return parent::__call($name, $args);
        }
    }

    /**
     * Overrides method from Zend_View
     * 
     * Fixes the handle of parent private property strictVars
     * 
     * @param boolean
     * @return ZendX_View_Autoescape
     */
    public function strictVars($flag = true)
    {
        $this->_strictVarsAutoescape = ($flag) ? true : false;

        return $this;
    }
    
}
