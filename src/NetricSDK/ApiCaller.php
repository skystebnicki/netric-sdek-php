<?php
namespace NetricSDK;

use NetricSDK\Cache\CacheInterface;
use NetricSDK\Entity\Schema\EntitySchemaV1;
use NetricSDK\Entity\Schema\SchemaFactory;
use NetricSDK\EntityCollection\EntityCollection;
use NetricSDK\Entity\EntityFactory;
use NetricSDK\Entity\Entity;
use NetricSDK\Entity\EntityGrouping;

/**
 * Main class used to make REST API calls to a netric server
 */
class ApiCaller implements ApiCallerInterface
{
	/**
	 * The version of API we are calling
	 */
	const API_VERSION = 2;

	/**
	 * The server host such as https://test.netric.com
	 *
	 * @param string
	 */
	private $server = "";

	/**
	 * The application ID that has been approved to access the API
	 *
	 * @param string
	 */
	private $applicationId = "";

	/**
	 * The private key of the application ID used to sign secure requests
	 *
	 * @param string
	 */
	private $applicationKey = "";

	/**
	 * Auth token used for making requsts to protected resources
	 *
	 * @param string
	 */
	private $authToken = "";

    /**
     * Cache to reduce the number of calls to the server
     *
     * @var CacheInterface
     */
	private $cache = null;

	/**
	 * Constructor will setup API connection credentials
	 *
	 * @param string $server The server we are connecting to
	 * @param string $applicationId A unique ID supplied to grant access to the API for a specific application
	 * @param string $applicationKey The private key used to sign all requests
     * @param CacheInterface $cache Optional cache used to cache requests
	 */
	public function __construct($server, $applicationId, $applicationKey, CacheInterface $cache = null)
	{
		$this->server = $server;
		$this->applicationId = $applicationId;
		$this->applicationKey = $applicationKey;
		$this->cache = $cache;
	}

	/**
	 * Save an entity to the backend
	 * 
	 * @param Entity $entity Either a new entity (with no id value set) or an existing entity
	 * @return bool true on success, false on failure
	 */
	public function saveEntity(Entity $entity)
	{
	    $schema = new EntitySchemaV1();
	    $data = $schema->getDataFromValues($entity);
		$ret = $this->sendRequest("entity", "save", $data);

		// Now set any values from the server
        $newSchema = SchemaFactory::getSchemaFromData($ret);
        $newSchema->setValuesFromData($entity, $ret);

		return true;
	}

	/**
	 * Delete an entity from the backend
	 * 
	 * @param Entity $entity Either a new entity (with no id value set) or an existing entity
	 * @return bool true on success, false on failure
     * @throws \Exception if an invalid entity was passed
	 */
	public function deleteEntity(Entity $entity)
	{
		if (!isset($entity->id) || !$entity->getType()) {
			throw new \Exception("Cannot delete an entity that does not yet exist");
		}

		$data = array(
			"obj_type" => $entity->getType(),
			"ids" => $entity->id
		);
		$ret = $this->sendRequest("entity", "remove", $data);
		return (is_array($ret) && (count($ret) > 0)) ? true : false;
	}

	/**
	 * Retrieve an entity by id
	 *
	 * @param string $objType The name of object this entity represents - like 'user'
	 * @param string $id the Unique id of the entity to load
	 * @return Entity the populated entity if found, or null if it does not exist
     * @throw \RuntimeException if the API request fails
	 */
	public function getEntity($objType, $id)
	{
		$data = [
			'obj_type'=>$objType, 
			'id'=>$id
		];
		$ret = $this->sendRequest("entity", "get", $data, 'GET', 3000);
		if (is_array($ret) && isset($ret['obj_type']) && isset($ret['id'])) {
            return $this->loadEntityFromData($ret);
        } else if (is_array($ret) && isset($ret['error'])) {
            throw new \RuntimeException("Error getting entity: " . $ret['error']);
		} else {
			return null;
		}
	}

	/**
	 * Retrieve an entity by id
	 *
	 * @param string $objType The name of object this entity represents - like 'user'
	 * @param string $id the Unique id of the entity to load
	 * @param array $namespaceCondtiions Optional namesapce conditions
	 * @return Entity the populated entity if found, or null if it does not exist
	 */
	public function getEntityByUniqueName($objType, $uname, array $namespaceCondtiions = [])
	{
		$data = [
			'obj_type'=>$objType, 
			'uname'=>$uname,
			'uname_conditions' => $namespaceCondtiions
		];
		$ret = $this->sendRequest("entity", "get", $data, 'POST',5000);
		if (is_array($ret) && isset($ret['obj_type']) && isset($ret['id'])) {
			return $this->loadEntityFromData($ret);
        } else if (is_array($ret) && isset($ret['error'])) {
            throw new \RuntimeException(
                "Error getting entity: " . $ret['error'] . ':' . var_export($data, true)
            );
        } else {
			return null;
		}
	}

	/**
	 * Get object definition based on an object type
	 *
     * @param string $objType The object type name
     * @param string $fieldName The field name to get grouping data for
	 * @return \Netric\Models\EntityGrouping[]
	 */
	public function getEntityGroupings($objType, $fieldName)
    {
        if (!$objType || !$fieldName)
            return array();
            
        $params = array("obj_type"=>$objType, "field_name"=>$fieldName);
		$groupsData = $this->sendRequest("entity", "get-groupings", $params, "GET", 5000);

        // Initialize heiarachial array of groupings
        if (isset($groupsData['groups'])) {
	        return $this->loadEntityGropingFromData($groupsData['groups']);
        } else {
        	return array();
        }
    }

	/**
	 * Query the backend for entities that match the passed query conditions and set the collection
	 *
	 * @param EntityCollection $collection A collection to query and set entities into
	 * @return int The number of entities retrieved in the current page
	 */
	public function loadCollection(EntityCollection $collection)
	{
		$queryData = array(
			'obj_type' => $collection->getType(),
			'offset' => $collection->getOffset(),
			'limit' => $collection->getLimit(),
		);

		// Add conditions to the query
		$queryData['conditions'] = array();
		$wheres = $collection->getWheres();
		foreach ($wheres as $where) {
			$queryData['conditions'][] = $where->toArray();
		}

		// Add order by
		$queryData['order_by'] = $collection->getOrderBy();

		// Call the server to get the query results
        $data = $this->sendRequest("entity-query", "execute", $queryData, 'POST', 3000);

		// Clear entities because we only want the current page loaded into memory
		$collection->clearEntities();
		$collection->setTotalNum($data['total_num']);
		foreach ($data['entities'] as $entityData) {
			$entity = $this->loadEntityFromData($entityData);
			$collection->addEntity($entity);
		}
		return $data['num'];
	}

	/**
	 * Get an authToken from the server
	 */
	public function getAuthToken()
	{
		$url = $this->server . "/api/" . self::API_VERSION . "/authentication/authenticate?";
		$url .= "username=" . urlencode($this->applicationId);
		$url .= "&password=" . urlencode($this->applicationKey);

		$ch = curl_init($url);
		// set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		// Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		// execute post and get results
		$resp = curl_exec($ch); 
		curl_close($ch);

		// Get data
		$retData = json_decode($resp, true);
		if ($retData['result'] != 'SUCCESS') {
			throw new \Exception("Auth failed: " . var_export($retData, true));
		}

		// Return JSON decoded response
		return $retData['session_token'];
	}

    /**
     * Set or unset a cache interface
     *
     * @param CacheInterface|null $cache
     */
	public function setCache(CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

	/**
     * Send a request using the php api for netric
     * 
     * @param string $controller Controller name to call
     * @param string $action The name of the action to call in the selected controller
     * @param array $data Params (assoc) to be sent to the controller
     * @param string $method Can either be GET or POST
     * @param int $cacheFor If set we will cache for this number of miliseconds
     * @return mixed -1 on failure, string resonse on success
     * @throws \Exception
     */
    private function sendRequest($controller, $action, $data, $method='POST', $cacheFor = 0)
	{
		$url = $this->server . "/api/" . self::API_VERSION . "/$controller/$action";

		if (!$this->authToken) {
			return $this->getAuthTokenThenSendRequest($controller, $action, $data, $method);
		}

		// If the method is GET then we should append query params
		if ($method === 'GET') {
			$urlQueryParams = "";
			foreach ($data as $pname=>$pval) {
				if ($urlQueryParams) {
					$urlQueryParams .= "&";
				}

				if (is_array($pval)) {
					foreach ($pval as $psubval) {
						$urlQueryParams .= $pname . "[]=" . urlencode($psubval);	
					}
				} else {
					$urlQueryParams .= $pname . "=" . urlencode($pval);
				}
			}
			if ($urlQueryParams) {
				$url .= "?" . $urlQueryParams;
			}
		}

        $cacheKey = $method . "-" . $action . "-" . $controller . md5(json_encode($data));

		// Check for caching
        if ($this->cache && $cacheFor) {
		    $cacheResponse = $this->cache->get($cacheKey);
		    if ($cacheResponse) {
		        return $cacheResponse;
            }
        }

		$headers = ['Authentication: ' . $this->authToken];

		$ch = curl_init($url);
		// set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		// Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($method === 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            // Make sure the header is set so netric knows to get the raw body
            $headers[] = 'Content-Type: application/json';
		}
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		// execute post and get results
		$resp = curl_exec($ch); 
		$decodedData = json_decode($resp, true);

		// If we are using cache then store the response for 3 seconds
        if ($this->cache && $cacheFor && !isset($decodedData['error'])) {
            $this->cache->set($cacheKey, $decodedData, $cacheFor);
        }

		curl_close($ch);

		// Return JSON decoded response
		return $decodedData;
	}

	/**
	 * Function called when we don't have a valid auth token
	 * 
	 * When it finishes it will call sendRequest again
	 *
	 * @param string $controller Controller name to call
     * @param string $action The name of the action to call in the selected controller
     * @param array $data Params (assoc) to be sent to the controller
     * @return mixed -1 on falure, string resonse on success
     * @throws \Exception If we were unable to get an auth token
     */
	private function getAuthTokenThenSendRequest($controller, $action, $data, $method)
	{
		// Call auth to get a token
		$this->authToken = $this->getAuthToken();

		if (!$this->authToken) {
			throw new \Exception("Could not get auth token for some reason");
		}

		return $this->sendRequest($controller, $action, $data, $method);
	}

	/**
	 * Initialze all the properties in an entity from data
	 * 
	 * @param array $data The data to load into the entity
	 * @return Entity An initialized entity from the data
	 */
	private function loadEntityFromData(array $data)
	{
		if (!isset($data['obj_type'])|| !isset($data['id'])) {
			return null;
		}

		$entity = EntityFactory::factory($data['obj_type'], $data['id']);
        $schema = SchemaFactory::getSchemaFromData($data);
        $schema->setValuesFromData($entity, $data);

		return $entity;
	}

	/**
     * Initialize heiarachial array of groupings
     * 
     * @param type $groupsData
     * @return EntityGrouping[]
     */
    private function loadEntityGropingFromData($groupsData)
    {
        $groupings = array();

        if ($groupsData && !isset($groupsData->error))
        {
            foreach ($groupsData as $grpData)
            {
                $grp = new EntityGrouping();
                
                foreach ($grpData as $fname=>$fval)
                {
                    switch($fname)
                    {
                    case "heiarch":
                        $fname = "isHeiarch";
                        break;
                    case "parent_id":
                        $fname = "parantId";
                        break;
                    case "sort_order":
                        $fname = "sortOrder";
                        break;
                    default:
                        break;
                    }
                    
                    $grp->setValue($fname, $fval);
                }
                
				if (isset($grpData->children))
                	$grp->children = $this->loadEntityGropingFromData($grpData->children);
                
                $groupings[] = $grp;
            }
        }
        
        return $groupings;
    }
}
