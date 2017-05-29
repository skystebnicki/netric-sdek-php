<?php
namespace NetricSDK\EntityCollection;

/**
 * Where repreesnts a 'where' condition in a collection
*/
class Where 
{
    /**
     * Combiner logic
     * 
     * @var string
     */
    public $bLogic = "and";
    
    /**
     * The field name
     * 
     * If the field name is "*" then conduct a full text query
     * 
     * @var string
     */
    public $fieldName = "";
    
    /**
     * The operator to use with this condition
     * 
     * @var string
     */
    public $operator = "";
    
    /**
     * The value to query against
     * 
     * @var string
     */
    public $value = "";

    /**
     * Define operators to comparing values
     */
    const OPERATOR_EQUAL_TO                  = 'is_equal';
    const OPERATOR_NOT_EQUAL_TO              = 'is_not_equal';
    const OPERATOR_LESS_THAN                 = 'is_less';
    const OPERATOR_LESS_THAN_OR_EQUAL_TO     = 'is_less_or_equal';
    const OPERATOR_GREATER_THAN              = 'is_greater';
    const OPERATOR_GREATER_THAN_OR_EQUAL_TO  = 'is_greater_or_equal';
    const OPERATOR_CONTAINS                  = 'contains';
    // TODO: add the rest here
    
    /**
     * Create a where condition
     * 
     * @param string $fieldName
     * @return \Netric\Models\Collection\Where
     */
    public function __construct($fieldName="*") 
    {
        $this->fieldName = $fieldName;
        return $this;
    }
    
    /**
     * Set condition to match where field equals value
     * 
     * @param string $value
     */
    public function equals($value)
    {
        $this->operator = "is_equal";
        $this->value = $value;
    }

    /**
     * Set condition to match where field does not equal value
     * 
     * @param string $value
     */
    public function doesNotEqual($value)
    {
        $this->operator = "is_not_equal";
        $this->value = $value;
    }

	/**
	 * Check if terms are included in a string - full text
	 *
	 * @param string $value
	 */
	public function contains($value)
	{
        $this->operator = "contains";
        $this->value = $value;
	}

	/**
	 * Check if terms are included in a string - full text
	 *
	 * @param string $value
	 */
	public function isGreaterThan($value)
	{
        $this->operator = "is_greater";
        $this->value = $value;
	}

    /**
     * Export this where condition to an array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'blogic' => $this->bLogic,
            'field_name' => $this->fieldName,
            'operator' => $this->operator,
            'value' => $this->value,
        );
    }
}
