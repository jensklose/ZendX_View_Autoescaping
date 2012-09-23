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
 * standard bean - dataContainer
 * 
 * @package     ZendX_View_Autoescape
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Bean extends ZendX_AbstractBean
{
    
    /**
     * Overrides AbstractBean method not defined properties can also write to
     * 
     * @param string $key
     * @param mixed $value 
     */
    public function setProperty($key, $value)
    {
        $this->{'_'.$key} = $value;
    }
    
}