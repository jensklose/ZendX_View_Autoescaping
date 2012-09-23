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
class ZendX_View_Facade_Bean 
    extends ZendX_View_AbstractFacade
{
    /**
     * @var ZendX_AbstractBean 
     */
    protected $_rawStructure = null;
    
    /**
     * deploys given data into the inner structure
     * 
     * @param mixed $data 
     */
    protected function _initData($data)
    {
        switch (gettype($data)) {
            case 'object':
                if ($data instanceof ZendX_AbstractBean) {
                    $this->_rawStructure = $data;
                }
                break;
            case 'array':
                $this->_rawStructure = new ZendX_View_Facade_Iterator($data, $this->_view);
                break;
        }
        
        if (null === $this->_rawStructure) {
            $this->_rawStructure = new ZendX_View_Facade_Iterator(null,  $this->_view);
        }
    }
    
    /**
     * Covers using in string context
     * 
     * @return string of classname 
     */
    public function __toString()
    {
        return 'ViewFacade';
    }
    
    /**
     * gets the unescaped property
     * 
     * @param string $key
     * @return mixed 
     */
    protected function _fetchRawValue($key)
    {
        if($this->_rawStructure instanceof ZendX_AbstractBean) {
            return $this->_rawStructure->getProperty(ltrim($key, '/'));
        }
        
        return $this->_rawStructure->getProperty(ltrim($key, '/'));
    }

}
