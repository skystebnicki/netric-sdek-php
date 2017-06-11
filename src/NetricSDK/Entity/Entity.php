<?php
/**
 * This is the default entity/object that will be instantiated if there is no subclassed entity
 */
namespace NetricSDK\Entity;

/**
 * Base/default entity
 *
 * This may be extended in the ./Type/* classes but that is usually not needed.
 * If you do need to extend it just be sure to add the new type to the EntityFactory
 */
class Entity
{
    /**
     * The values for the fields of this entity
     *
     * If the value is scalar then it will just store the value
     * 
     * If the value is object, or fkey then the value will be:
     *  array('id'=>'label')
     *
     * If the value is an object_multi or fkey_multi then it will be:
     *  array(array('id'=>'label'), array('id2'=>'label2'))
     * 
     * @var array
     */
    protected $values = [];
    
    /**
     * Set object type
     * 
     * @var string
     */
    protected $objType = "";
    
    /**
     * Class constructor
     * 
     * @param string $objType Unique name of the objec type
     */
    public function __construct($objType) 
    {
        $this->objType = $objType;
    }
    
    /**
     * Get the object type of this object
     * 
     * @return string
     */
    public function getType()
    {
        return $this->objType;
    }
    
    /**
     * Magic metghod for setting a value
     *
     * @param string $name The name of the field to set
     * @param mixed $value Can be any scalar type or an array
     */
    public function __set($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Magic method for getting a value
     *
     * @param string $name The name of the field to set
     * @return mixed Property value
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**
     * Check if a field value has been set
     */
    public function __isset($name)
    {
        return isset($this->values[$name]);
    }

    /**  
     * Unset a field value
    */
    public function __unset($name)
    {
        unset($this->values[$name]);
    }

    /**
     * Return either the string or an array of values if *_multi
     * 
     * @param string $strname
     * @return string|array
     */
    public function getValue($strname)
    {
        return (isset($this->values[$strname])) ? $this->values[$strname] : null;
    }
    
    /**
     * Set a field value for this object
     * 
     * @param string $strName
     * @param mixed $value
     * @param string $valueName If this is an object or fkey then cache the foreign value
     */
    public function setValue($strName, $value, $valueName=null)
    {
        $this->values[$strName] = $value;        
    }

    /**
     * Get all values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
    
	/**
	 * Get display name for this entity based on common name fields
	 */
	public function getName()
	{
		$fields = array(
			"name",
			"title",
			"subject",
		);

		foreach ($fields as $fname)
		{
			if ($this->getValue($fname))
				return $this->getValue($fname);
		}

		return $this->getId();
	}

	/**
     * Generate a teaser text for this entity
     * 
     * @param string $wordLengh The maximum number of words to return
     * @return string The teaster
     */
    public function getTeaser($wordLength=25)
    {
		$val = "";
		$fields = array(
			"data",
			"description",
			"body",
			"notes",
		);

		foreach ($fields as $fname)
		{
			if ($this->getValue($fname))
				$val = $this->getValue($fname);
		}

		if ($val)
        	return implode(' ', array_slice(explode(' ', strip_tags($val, "<br/>")), 0, $wordLength));
		else
			return "";
    }
}
