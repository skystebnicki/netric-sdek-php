<?php
namespace NetricSDK\Entity\Schema;

use NetricSDK\Entity\Entity;
use NetricSDK\Entity\EntityOrGroupReference;

/**
 * Version 1 of netric API schema mapping data to and from an entity
 */
class EntitySchemaV1 implements EntitySchemaInterface
{
    /**
     * Get the version of this entity schema
     *
     * @return int
     */
    public function getSchemaVersion()
    {
        return 1;
    }

    /**
     * Export an entity to an associative array matching schema version 1
     *
     * @param Entity $entity
     */
    public function getDataFromValues(Entity $entity)
    {
        $values = $entity->getValues();
        $retData = ['obj_type' => $entity->getType()];

        foreach ($values as $fieldName=>$fieldValue) {
            if (is_array($fieldValue)) {
                foreach ($fieldValue as $key=>$multiValue) {
                    if (is_a($multiValue, EntityOrGroupReference::class)) {
                        $retData[$fieldName][] = $multiValue->id;
                        $retData[$fieldName . "_fval"][] = [
                            "id"=>$multiValue->id,
                            "name"=>$multiValue->name
                        ];
                    } else {
                        $retData[$fieldName][] = $multiValue;
                    }
                }
            } else if (is_object($fieldValue)) {
                if (is_a($fieldValue, EntityOrGroupReference::class)) {
                    $retData[$fieldName] = $fieldValue->id;
                    $retData[$fieldName . "_fkey"] = $fieldValue->name;
                } else if (is_a($fieldValue, \DateTime::class)) {
                    $retData[$fieldName] = $fieldValue->format("Y-m-d H:i:s Z");
                }
            } else {
                $retData[$fieldName] = $fieldValue;
            }
        }

        return $retData;
    }

    /**
     * Set values in an entitty from an associative array
     *
     * @param Entity $entity
     * @param array $data
     */
    public function setValuesFromData(Entity $entity, array $data)
    {
        foreach ($data as $fieldName=>$fieldValue) {
            // We don't want to set _fval fields since they are not real entity fields
            if (substr($fieldName, -5, 5) != '_fval') {
                // If we are working with fkey, fkey_multi, object, object_multi, then use _fval version
                if (isset($data[$fieldName . "_fval"])) {
                    $fieldValues = null;
                    // Check if the field is as multi_value field
                    if (is_array($fieldValue)) {
                        $fieldValues = [];
                        foreach ($data[$fieldName . "_fval"] as $id=>$name) {
                            $fieldValues[] = new EntityOrGroupReference($id, $name);
                        }
                    } else {
                        // There should only be one element in the *_fval field but we foreach for the key
                        foreach ($data[$fieldName . "_fval"] as $id=>$name) {
                            $fieldValues = new EntityOrGroupReference($id, $name);
                        }
                    }

                    $entity->$fieldName = $fieldValues;

                } else {
                    $entity->$fieldName = $fieldValue;
                }
            }
        }
    }
}
