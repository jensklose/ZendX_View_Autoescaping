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
 * Facade string types to be ready to use in views
 * 
 * @package     ZendX_View_Autoescape
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_View_Facade_String
    extends ZendX_View_AbstractFacade
{
    /**
     * @var string
     */
    private $_raw = null;

    /**
     * Constructor: deploys given data as string into the objectvar
     * 
     * @param type mixed
     */
    protected function _initData($data)
    {
        $this->_raw = (string) $data;
    }

    /**
     * Covers using in string context
     *
     * @return string of classname
     */
    public function __toString()
    {
        return $this->_escapeForContext($this->_raw, $this->_escapingContext);
    }

    /**
     * no deeper Properties, so do nothing
     * 
     * @param string $key
     * @return string 
     */
    protected function _fetchRawValue($key)
    {
        return '';
    }

}
