<?php
/**
 * This class will be used to instantiate entities
 */
namespace NetricSDK\Entity;

use NetricSDK\Entity\Type\CustomerEntity;
use NetricSDK\Entity\Type\ContentFeedPostEntity;
use NetricSDK\Entity\Type\InfocenterDocumentEntity;
use NetricSDK\Entity\Entity as BaseEntity;

/**
 * Factory class
 */
class EntityFactory
{
    /**
     * This function is responsible for loading subclasses or the base class
     * 
     * @param string $objType
     * @param string $oid
     */
    public static function factory($objType, $oid="")
    {
        switch ($objType)
        {
        case "content_feed_post":
            return new ContentFeedPostEntity();
        case "infocenter_document":
            return new InfocenterDocumentEntity();
        case "customer":
            return new CustomerEntity();
        default:
            return new BaseEntity($objType);
        }        
    }
}
