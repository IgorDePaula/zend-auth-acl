<?php

namespace ZfCommons;

use Zend\Mvc\MvcEvent,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter,
    Zend\Authentication\AuthenticationService,
    Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements
AutoloaderProviderInterface, ConfigProviderInterface {

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('dispatch', array($this, 'loadConfiguration'),2);
        //you can attach other function need here...
    }

    public function loadConfiguration(MvcEvent $e) {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) use ($sm) {
                    $sm->get('ControllerPluginManager')->get('MyPlugin')
                            ->doAuthorization($e); //pass to the plugin...    
                }
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            // 'db' => array(
            // 'username' => 'YOUR USERNAME HERE',
// 'password' => 'YOUR PASSWORD HERE',
// 'driver' => 'Pdo',
// 'dsn' => 'mysql:dbname=zf2tutorial;host=localhost',
// 'driver_options' => array(
// PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
// ),
// ),



            'factories' => array(
                'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
                '\ZfCommons\Model\MyAuthStorage' => function($sm) {
                    return new \ZfCommons\Model\MyAuthStorage('zf_tutorial');
                },                
                'AuthService' => function($sm) {
                   
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter,'users', 'username', 'password', 'MD5(?)');

                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $authService->setStorage($sm->get('ZfCommons\Model\MyAuthStorage'));

                    return $authService;
                },
            ),
        );
    }

}
