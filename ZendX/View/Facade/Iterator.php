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
 * Mapping for Array types to be ready to use in views
 * 
 * @package     ZendX_View_Autoescape
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_View_Facade_Iterator 
    extends ZendX_View_AbstractFacade
    implements Countable,Iterator
{
    /**
     * @var array
     */
    private $_arrayContext;
    /**
     * @var ZendX_Bean
     */
    private $_beanContext = null;

    /**
     * deploys data array into bean object
     * 
     * @param array $data
     * @return boolean 
     */
    protected function _initData($data)
    {
        $this->_beanContext = new ZendX_Bean($data);
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $this->setProperty($key, $value);
        }
        if (null === $this->_arrayContext) {
            $this->_arrayContext = array();
        }
    }

    /**
     * Covers using in string context
     *
     * @return string of classname
     */
    public function __toString()
    {
        return 'ViewIterator';
    }

    /**
     * set given property in the bean and array context
     *
     * @param string $key
     * @param mixed $value
     */
    public function setProperty($key, $value)
    {
        $this->_beanContext->setProperty($key, $value);
        $this->_arrayContext[$key] = $value;
    }

    /**
     * counts all elements
     *
     * @return int
     */
    public function count()
    {
        return count($this->_arrayContext);
    }

    /**
     * returns the current property
     * 
     * @return mixed
     */
    public function current()
    {
        $key = key($this->_arrayContext);
        return $this->getProperty($key);
    }

    /**
     * returns the escaped key of the current property
     *
     * @return mixed
     */
    public function key()
    {
        return $this->_escapeForContext(key($this->_arrayContext), $this->_escapingContext);
    }

    /**
     * sets the pointer to the next property
     */
    public function next()
    {
        next($this->_arrayContext);
    }

    /**
     * sets the pointer to the first property
     */
    public function rewind()
    {
        reset($this->_arrayContext);
    }

    /**
     * validates current property
     * 
     * @return boolean
     */
    public function valid()
    {
        return null !== key($this->_arrayContext);
    }
    
    /**
     * returns a certain raw property
     * 
     * @param string $key
     * @return mixed
     */
    protected function _fetchRawValue($key)
    {
        return $this->_beanContext->getProperty(ltrim($key, '/'));
    }

}
