<?php
namespace NetricTest;

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/../vendor/autoload.php';


/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    public static function init()
    {
        //static::initAutoloader();
    }

    protected static function initAutoloader()
    {
            
        $autoLoader = new StandardAutoloader(array(
            'namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
            ),
            'fallback_autoloader' => true,
        ));
        $autoLoader->register();

    }
}

Bootstrap::init();