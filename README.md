# Netric SDK for PHP

This SDK can be used to interact with netric. In order to use it you will need to obtain an applicationId and a key from the netric admin.

## Example of querying a bunch of entities

    // Initialize the API
    $netricApi = new NetricSDK\NetricApi("https://test.netric.com", $appId, $key);

    // Create a new collection
    $entityCollection = $netricApi->createEntityCollection();
    $entityCollection->where("name")->equals("sky");
    $entityCollection->offset(50);
    $entityCollection->limit(100);

    // Make the request to the server and print results
    $numEntities = $entityCollection->load();
    for ($i = 0; $i < $numEntities; $i++) {
    	$entity = $entityCollection->getEntity($i);
    	echo "Got " . $entity->name;
    }

## Example of getting a specific entity

    // Initialize the API
    $netricApi = new NetricSDK\NetricApiApi("https://test.netric.com", $appId, $key);

    // Load the entity data from the server
    $entity = $netricApi->getEntity("user", "123");
    echo $entity->name;

## Example of creating a new entity and saving it

    // Create a new entity
    $entity = new NetricSDK\Entity("comment");
    $entity->name = "Sky";

    // Initialize the API and save the entit
    $netricApi = new NetricSDK\NetricApi("https://test.netric.com", $appId, $key);
    $netricApi->saveEntity($entity);
