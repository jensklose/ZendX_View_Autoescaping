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
 * abstract baseclass for beans - datacontainer for applications
 * 
 * @package     ZendX_View_Autoescape
 * @subpackage  Beans
 * @author      Jens Klose <jens.klose@gmail.com>
 * @author      Marcel Kilian <cdskilian@googlemail.com>
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class ZendX_AbstractBean
{
    /**
     * the bean path seperator 
     */
    CONST PATH_SEPERATOR = '/';
    
    /**
     * prefix for bean properties
     */
    CONST PREFIX_BEAN_PROPERTIES = '_';
    
    /**
     * Constructor: if $data is given it will set the content of $data into the
     * bean
     * 
     * @param array $data
     */
    public function __construct($data = NULL)
    {
        // set Bean content with the given $data
        if($data) {
            $this->setBean($data);
        }
    }
    
    /**
     * Deploys given $data array into bean properties.
     * Uses the key and value pairs.
     * 
     * @param array $data
     * @return boolean 
     */
    public function setBean($data)
    {
        if(!is_array($data)) {
            return FALSE;
        }

        // set $data content as bean properties
        foreach($data as $property => $value ) {
            $this->setProperty($property, $value);
        }
        
        return TRUE;
    }
    
    /**
     * sets a certain bean property.
     * 
     * @param string $propertyPath
     * @param mixed $value
     */
    public function setProperty($propertyPath, $value)
    {
        // init
        $disProperty = $this->_dissolvePropertyPath($propertyPath);
        // first property is this object
        $property = $this;

        // follow property path
        foreach($disProperty as $subProperty) {
            
            // processing depending on property type
            if (is_array($property)) {
                $property = & $this->_setPropertyArray($property, $subProperty);
            } elseif ($property instanceof ZendX_AbstractBean) {
                $property = & $property->_setPropertyBean($subProperty);
            } elseif (is_object($property)) {
                $property = & $this->_setPropertyGenericObject($property, $subProperty);
            }
        }
        
        $property = $value;
    }
    
    /**
     * gets a certain property.
     * 
     * @param string $propertyPath
     * @return mixed
     */
    public function getProperty($propertyPath, $enableExceptions = FALSE)
    {
        // init
        $disProperty = $this->_dissolvePropertyPath($propertyPath);
        // first property is this object
        $property = $this;
        
        // follow property path
        foreach ($disProperty as $subProperty) {

            // processing depending on property type
            try {
                if (is_array($property)) {
                    $property = $this->_getPropertyArray($property, $subProperty);
                } elseif ($property instanceof ZendX_AbstractBean) {
                    $property = $property->_getPropertyBean($subProperty);
                } elseif (is_object($property)) {
                    $property = $this->_getPropertyGenericObject($property, $subProperty);
                } else {
                    $property = NULL;
                }
            } catch (Exception $e) {
                if ($enableExceptions) {
                    throw $e;
                }

                return NULL;
            }
        }
        
        return $property;
    }
    
    /**
     * checks that property exists. The Property also exists if the value is Null.
     * It will only return false if the property don't exists
     * 
     * @param string $propertyPath
     * @return boolean 
     */
    public function hasProperty($propertyPath)
    {
        try{
            $this->getProperty($propertyPath, TRUE);
        } catch (Exception $e) {
            return FALSE;
        }
        
        return TRUE;
    }
    
    
    /**
     * property seter for arrays. Also sets array key if not existing.
     * 
     * @param array $array
     * @param string $key
     * @return array as referance
     */
    protected function & _setPropertyArray(& $array, $key)
    {
        // create property if it doesnt exist
        if(!isset($array[$key])) {
            $array[$key] = array();
        }
        
        return $array[$key];
    }
    
    /**
     * property geter for arrays.
     * 
     * @param type $array
     * @param type $key
     * @return type 
     */
    protected function _getPropertyArray($array, $key)
    {
        return isset($array[$key]) ? $array[$key] : null;
    }
    
    /**
     * property seter for beans. Also sets bean property if not existing.
     * 
     * @param string $property
     * @return ZendX_AbstractBean
     */
    protected function & _setPropertyBean($property)
    {
        $propertyName = self::PREFIX_BEAN_PROPERTIES . $property;
        
        // create property if it doesnt exist
        if(!property_exists($this, $propertyName)) {
            $this->$propertyName = array();
        } elseif ($this->$propertyName === null) {
            $this->$propertyName = array();
        }
        
        return $this->$propertyName;
    }
    
    protected function _getPropertyBean($property)
    {
        $propertyName = self::PREFIX_BEAN_PROPERTIES . $property;
        if(!property_exists($this, $propertyName)) {
            throw new InvalidArgumentException();
        }
        
        return $this->$propertyName;
    }
    
    /**
     * property seter for normal objects. Also sets property if not existing.
     * 
     * @param object $genericObject
     * @param string $key
     * @return object
     */
    protected function & _setPropertyGenericObject($genericObject, $key)
    {
        if(!property_exists($genericObject, $key)) {
            $genericObject->$key = array();
            if (!is_array($genericObject->$key)) {
                throw new ErrorException('bean accessor not supported by ' . get_class($genericObject));
            }
        }

        return $genericObject->$key;
    }
    
    /**
     * property geter for normal objects.
     * 
     * @param object $genericObject
     * @param string $property
     * @return mixed
     */
    protected function _getPropertyGenericObject($genericObject, $property)
    {
        return $genericObject->$property;
    }
    
    
    /**
     * dissolves the property path into the sub properties.
     * returns an array for foreach usage
     * 
     * @param string $propertyPath
     * @return array
     */
    protected function _dissolvePropertyPath($propertyPath)
    {
        // remove slash at the beginning of the string
        if(mb_strpos($propertyPath, self::PATH_SEPERATOR) === 0) {
            $propertyPath = mb_substr($propertyPath, 1);
        }
        
        // dissolve the propertyPath
        return explode(self::PATH_SEPERATOR, $propertyPath);
    }
    
    /**
     * returns all bean properties in array form. Bean objects wont be dissolved.
     * 
     * @return array 
     */
    public function asArray()
    {
        $array = array();
        $vars = get_object_vars($this);

        // run through each property first lvl
        foreach ($vars as $property => $propertyValue) {

            // match property names
            if (mb_strpos($property, self::PREFIX_BEAN_PROPERTIES) === 0) {
                $array[mb_substr($property, 1)] = $propertyValue;
            } else {
                $array[$property] = $propertyValue;
            }
        }
        
        return $array;
    }
    
    /**
     * returns all bean properties in array form and dissolves each bean object
     * in it.
     * 
     * @return array
     */
    public function asDeepArray()
    {
        $array = array();

        foreach (get_object_vars($this) as $name => $value) {
            if (mb_strpos($name, self::PREFIX_BEAN_PROPERTIES) === 0) {
                $result = $this->$name;

                if (isset($result)) {
                    if ($result instanceof ZendX_AbstractBean)
                        $result = $result->asDeepArray();
                    elseif (is_array($result)) {
                        foreach ($result as $key => $value) {
                            if ($value instanceof ZendX_AbstractBean) {
                                $value = $value->asDeepArray();
                            }
                            $result[$key] = $value;
                        }
                    }
                }
                $array[mb_substr($name, 1)] = $result;
            }
        }

        return $array;
    }
    
}
