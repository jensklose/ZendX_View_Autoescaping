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
 * Facade NULL or emtpy types to be ready to use in views
 * 
 * @package     ZendX_View_Autoescape
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_View_Facade_Null
    extends ZendX_View_AbstractFacade
    implements Countable,Iterator
{
    /**
     * stay empty
     * 
     * @param mixed $data 
     */
    protected function _initData($data)
    {
    }

    /**
     * Covers using in string context
     *
     * @return string of classname
     */
    public function __toString()
    {
        return '';
    }

    /**
     * Overrides AbstractBean method not defined properties can also write to
     *
     * @param string $key
     * @param mixed $value
     */
    public function setProperty($key, $value)
    {
    }

    /**
     * @return int 
     */
    public function count()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function current()
    {
        return '';
    }

    /**
     * @return null 
     */
    public function key()
    {
        return null;
    }

    /**
     * @return void
     */
    public function next()
    {
    }

    /**
     * @return void 
     */
    public function rewind()
    {
    }

    /**
     * return false cause null properties are not valid
     * 
     * @return boolean 
     */
    public function valid()
    {
        return false;
    }
    
    /**
     * @return null 
     */
    protected function _fetchRawValue($key)
    {
        return null;
    }

}
