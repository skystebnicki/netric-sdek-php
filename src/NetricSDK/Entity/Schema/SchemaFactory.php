<?php
namespace NetricSDK\Entity\Schema;

/**
 * Determine the schema version from data and return it
 */
class SchemaFactory
{
    /**
     * Get a schema from data passed in
     *
     * If not schema is set it will assume data is imported and exported from v1
     * since the version is required for 2 and newer schemas
     *
     * @param array $data
     * @return EntitySchemaInterface
     */
    static public function getSchemaFromData(array $data)
    {
        // Schema versions have not been implemented yet, but we may want to in the future
        // $version = (isset($data['schema_version'])) ? $data['schema_version'] : 1;
        // We can add future versions here
        return new EntitySchemaV1();
    }
}