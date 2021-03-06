<?php
namespace NetricSDK\EntityCollection;

use NetricSDK\ApiCallerInterface;
use NetricSDK\DataMapper\DataMapperInterface;
use NetricSDK\Entity\Entity;

/**
 * A collection of entities optionally filtered by where conditions
 */
class EntityCollection
{
	/**
     * The object type we are working with
     * 
     * @var string
     */
    private $objType = "";
    
    /**
     * Array of where conditions
     * 
     * @var array [['blogic', 'field', 'operator', 'value']]
     */
    private $wheres = array();
    
    /**
     * Order by fields
     * 
     * @var array [['field', 'direction']]
     */
    private $orderBy = array();
    
    /**
     * API Caller used for making requests to the server
     * 
     * @var ApiCallerInterface
     */
    private $apiCaller = null;
    
    /**
     * Array of entities that are loaded in this collection
     * 
     * @param Entity
     */
    private $entities = array();
    
    /**
     * Limit number of entities loaded from datamapper per page
     * 
     * @var int
     */
    private $limitPerPage = 100;
    
    /**
     * The current offset
     * 
     * @var int
     */
    private $offset = 0;
    
    /**
     * The starting offset of the next page
     * 
     * This is set by the datamapper when the query is done
     * 
     * @var int
     */
    private $nextPageOffset = -1;
    
    /**
     * The starting offset of the previous page
     * 
     * This is set by the datamapper when the query is done
     * 
     * @var int
     */
    private $prevPageOffset = -1;
    
    /**
     * Total number of entities in the collection
     * 
     * @var int
     */
    private $totalNum = 0;
    
    /**
     * Aggregations to use with this query
     * 
     * @var Netric\Models\Collection\Aggregation\AggregationInterface[]
     */
    private $aggregations = array();
    
    /**
     * Class constructor
     * 
     * @param string $type Unique name of the object type we are querying
     */
    public function __construct($type) 
    {
        $this->objType = $type;
    }
    
    /**
     * Get the object type for this collection
     * 
     * @return string
     */
    public function getType()
    {
        return $this->objType;
    }
    
    /**
     * Add a where condition
     * 
     * @param string $fieldName
     * @return \Netric\Models\Collection\Where
     */
    public function where($fieldName)
    {
        return $this->andWhere($fieldName);
    }

    /**
     * Add a where condition
     * 
     * @param string $fieldName
     * @return \Netric\Models\Collection\Where
     */
    public function andWhere($fieldName)
    {
        $where = new Where($fieldName);
        $this->wheres[] = $where;
        return $where;
    }

    /**
     * Add a where condition with 'or' blogic
     * 
     * @param string $fieldName
     * @return \Netric\Models\Collection\Where
     */
    public function orWhere($fieldName)
    {
        $where = new Where($fieldName);
		$where->bLogic = "or";
        $this->wheres[] = $where;
        return $where;
    }
    
    /**
     * Get array of wheres used to filter this collection
     * 
     * @return Netric\Models\Collection\Where[]
     */
    public function getWheres()
    {
        return $this->wheres;
    }

    /**
     * Add a field to order this by
     * 
     * @param string $fieldName
     * @param string $direction
     * @return self
     */
    public function orderBy($fieldName, $direction="ASC")
    {
        $this->orderBy[] = array(
            "field_name" => $fieldName,
            "direction" => $direction,
        );
        
        return $this;
    }
    
    /**
     * Get array of order by used to filter this collection
     * 
     * @return array(array("field", "direction"))
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }
    
    /**
     *  Set local reference an api caller for loading objects and auto pagination from the server
     * 
     * @param ApiCallerInterface $apiCaller
     */
    public function setApiCaller($apiCaller)
    {
        $this->apiCaller = $apiCaller;
    }
    
    /**
     * Restrict the number of entities that can be loaded per page
     * 
     * @param int $num Number of items to load per page
     */
    public function setLimit($num)
    {
        $this->limitPerPage = $num;
    }
    
    /**
     * Get the limit per page that can be loaded
     * 
     * @return int
     */
    public function getLimit()
    {
        return $this->limitPerPage;
    }
    
    /**
     * Get the offset of the next page for automatic pagination
     * 
     * @return int $offset
     */
    public function getNextPageOffset()
    {
        return $this->nextPageOffset;
    }
    
    /**
     * Get the offset of the previous page for automatic pagination
     * 
     * @return int $offset
     */
    public function getPrevPageOffset()
    {
        return $this->prevPageOffset;
    }
    
    /**
     * Set the offset
     * 
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    /**
     * Get current offset
     * 
     * @return int $offset
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Set the total number of entities for the defined query
     * 
     * The collection will load one page at a time
     * 
     * @param int $num The total number of entities in this query collection
     */
    public function setTotalNum($num)
    {
        $this->totalNum = $num;
    }
    
    /**
     * Get the total number of entities in this collection
     * 
     * @return int Total number of entities
     */
    public function getTotalNum()
    {
        return $this->totalNum;
    }
    
    /**
     * Add an entity to this collection
     * 
     * @param Entity $entity
     */
    public function addEntity(Entity $entity)
    {
        $this->entities[] = $entity;
    }
    
    /**
     * Reset the entities array
     */
    public function clearEntities()
    {
        $this->entities = array();
    }
    
    /**
     * Retrieve an entity from the collection
     * 
     * @param int $offset The offset of the entity to get in the collection
     * @return Entity
     */
    public function getEntity($offset=0)
    {
        if ($offset >= ($this->offset + $this->limitPerPage) || $offset < $this->offset)
        {
            // Get total number of pages
			$leftover = $this->totalNum % $this->limitPerPage;
			if ($leftover)
				$numpages = (($this->totalNum - $leftover) / $this->limitPerPage) + 1;
			else
				$numpages = $this->totalNum / $this->limitPerPage;
			
            // Get current page offset
            $page = floor($offset / $this->limitPerPage);
            if ($page)
                $this->setOffset($page * $this->limitPerPage);
            else
                $this->setOffset(0);
            
            // Automatially load the next page
            if ($this->apiCaller)
                $this->apiCaller->loadCollection($this);
        }
        
        // Adjust offset for pagination
        $offset = $offset - $this->offset;
        
        if ($offset >= count($this->entities))
            return null; // TODO: can expand to get next page for progressive load
        
        return $this->entities[$offset];
    }


    
    /**
     * Add a facet count to the list of facets
     * 
     * @param type $facetName
     * @param type $term
     * @param double $count
     */
    public function addFacetCount($facetName, $term, $count)
    {
        // TODO: handle facets
    }
    
    /**
     * Execute the query for this collection
     * 
     * @return boolean|int Number of entities loaded if success and datamapper is set, false on failure
     */
    public function load()
    {
        if ($this->apiCaller) {
            return $this->apiCaller->loadCollection($this);
        }
        else
            return false;
    }
    
    /**
     * Set aggregation data
     * 
     * @param string $name The unique name of this aggregation
     * @param int|string|array $value
     *
    public function setAggregation($name, $value)
    {
        $this->aggregations[$name] = $value;
    }
     * 
     */
    
    /**
     * Get aggregation data for this query by name
     * 
     * @return array()
     */
    public function getAggregation($name)
    {
        if (isset($this->aggregations[$name]))
            return $this->aggregations[$name];
        else
            return false;
    }
    
    /**
     * Get aggregations data for this query
     * 
     * @return array("name"=>array(data))
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
    
    /**
     * Check if this query has any aggregations
     * 
     * @return bool true if aggs exist, otherwise false
     */
    public function hasAggregations()
    {
        return (count($this->aggregations)>0) ? true : false;
    }
    
    /**
     * Add aggregation to this query
     * 
     * @param Netric\EntityQuery\Aggregation\AbstractAggregation
     */
    public function addAggregation(Collection\Aggregation\AbstractAggregation $agg)
    {
        $this->aggregations[$agg->getName()] = $agg;
    }

    /**
     * Get a has for this collection
     *
     * @return string Unique has for this collection
     */
    public function getHash()
    {
        $body = "";

        foreach ($this->wheres as $where) {
            $body .= json_encode($where->toArray());
        }

        foreach ($this->orderBy as $orderBy) {
            $body .= json_encode($orderBy);
        }

        $body .= $this->offset;
        $body .= $this->limitPerPage;

        $signature = md5 ( $body);

        // Keep it short, it should be unique enough
        if (strlen($signature) > 32) {
            $signature = substr($signature, 0, 32);
        }

        return $signature;
    }
}