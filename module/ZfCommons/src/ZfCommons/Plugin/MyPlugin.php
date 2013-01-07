<?php

namespace ZfCommons\Plugin;
 
use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\Session\Container as SessionContainer,
    Zend\Permissions\Acl\Acl,
    Zend\Permissions\Acl\Role\GenericRole as Role,
    Zend\Mvc\MvcEvent,
    Zend\Permissions\Acl\Resource\GenericResource as Resource;
    
class MyPlugin extends AbstractPlugin
{
    protected $sesscontainer ;

    private function getSessContainer()
    {
        if (!$this->sesscontainer) {
            $this->sesscontainer = new SessionContainer('zf_tutorial');
        }
        return $this->sesscontainer;
    }
    
    public function doAuthorization(MvcEvent $e)
    {
        //setting ACL...
        $acl = new Acl();
        //add role ..
        $acl->addRole(new Role('anonymous'));
        $acl->addRole(new Role('user'),  'anonymous');
        $acl->addRole(new Role('admin'), 'user');
        
        $acl->addResource(new Resource('Application'));
        $acl->addResource(new Resource('Login'));
        $acl->addResource(new Resource('Teste'));
        $acl->addResource(new Resource('ZfCommons'));
        
        $acl->deny('anonymous', 'Application', 'view');
        $acl->allow('anonymous', 'Login', 'view');
        
        $acl->allow('user',
            array('Application','Teste','ZfCommons'),
            array('view','index')
        );
        
        //admin is child of user, can publish, edit, and view too !
        $acl->allow('admin',
            array('Application','ZfCommons'),
            array('publish', 'edit')
        );
       
        $controller = $e->getTarget();
      
        $controllerClass = get_class($controller);
        $namespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
       
        $role = (!isset($this->getSessContainer()->storage->role )) ? 'anonymous' : $this->getSessContainer()->storage->role;
    
        if ( ! $acl->isAllowed($role, $namespace, 'index')){
            $router = $e->getRouter();
            $url    = $router->assemble(array(), array('name' =>'login'));
       
            $response = $e->getResponse();
//            $response->setStatusCode(302);
            //redirect to login route...
           $response->getHeaders()->addHeaderLine('Location', $url);    
           
        }
    }
}
