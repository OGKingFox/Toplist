<?php
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin {

    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    public function getAcl() {
        if (!isset($this->persistent->acl)) {
            $acl = new AclList();
            $acl->setDefaultAction(Acl::DENY);

            $roles = [
                'admins'  => new Role('Admin'),
                'members' => new Role('Member'),
                'guests'  => new Role('Guest')
            ];

            foreach ($roles as $role) {
                $acl->addRole($role);
            }

            $publicResources = array(
                'index'      => array('index', 'logout'),
                'login'      => array('index', 'auth'),
                'register'   => array('index'),
                'recover'    => array('index'),
                'errors'     => array('show401', 'show404', 'show500'),
            );

            $privateResources = array(
                'logout'     => array('index')
            );

            $adminResources = array(
                'admin'     => array(
                    'index', 'payments', 'users', 'products', 'categories', 'convert',
                    'add', 'edit', 'delete', 'view', 'user', 'banlist'
                )
            );

            foreach ($publicResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            foreach ($privateResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            foreach ($adminResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            // grants all roles access to public areas
            foreach ($roles as $role) {
                foreach ($publicResources as $resource => $actions) {
                    foreach ($actions as $action){
                        $acl->allow($role->getName(), $resource, $action);
                    }
                }
            }

            //Grant access to private area to role Users
            foreach ($privateResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('Member', $resource, $action);
                    $acl->allow('Admin', $resource, $action);
                }
            }

            //Grant access to admin area to role Admins
            foreach ($adminResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('Admin', $resource, $action);
                }
            }

            //The acl is stored in session, APC would be useful here too
            $this->persistent->acl = $acl;
        }

        return $this->persistent->acl;
    }

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
        $controller = $dispatcher->getControllerName();
        $action     = $dispatcher->getActionName();
        $role       = "Guest";

        if ($this->session->has('auth')) {
            /*if ($this->dispatcher->getControllerName() == "login"
                    || $this->dispatcher->getControllerName() == "register") {
                $this->response->redirect("");
                return false;
            }*/

            $userInfo = $this->apiRequest("https://discordapp.com/api/users/@me");

            $userinfo = $this->session->get("auth");
        }

        $acl = $this->getAcl();

        if (!$acl->isResource($controller)) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'show404'
            ]);
            return false;
        }

        $allowed = $acl->isAllowed($role, $controller, $action);

        if (!$allowed) {
            /*$this->dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'show401'
            ]);
            return false;*/
        }
    }

}
