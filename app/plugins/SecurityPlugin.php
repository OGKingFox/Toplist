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
                'admins'  => new Role('admin'),
                'members' => new Role('member'),
                'guests'  => new Role('guest')
            ];

            foreach ($roles as $role) {
                $acl->addRole($role);
            }

            $publicResources = array(
                'index'      => array('index', 'logout'),
                'login'      => array('index', 'auth', 'logout'),
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
                    $acl->allow('member', $resource, $action);
                    $acl->allow('admin', $resource, $action);
                }
            }

            //Grant access to admin area to role Admins
            foreach ($adminResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('admin', $resource, $action);
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

        if ($this->session->has('access_token')) {
            if ($this->dispatcher->getControllerName() == "login") {
                $this->response->redirect("");
                return false;
            }

            $userInfo = (new RestClient())
                ->setEndpoint("users/@me")
                ->setAccessToken($this->session->get("access_token"))
                ->setUseKey(true)
                ->submit(false);

            if (!$userInfo || $userInfo->code) {
                $this->session->destroy();
                $this->response->redirect("");
                return false;
            }

            $user = Users::getUser($userInfo->id);

            if (!$user) {
                $user = Users::createUser($userInfo);
                $user->save();
            }

            $role = $user->getRole();
            $this->view->user = $user;
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

        return true;
    }

}
