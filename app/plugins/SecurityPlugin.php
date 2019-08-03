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

        $public = [
            'index'      => ['index', 'logout', 'view', 'like', 'report', 'stats'],
            'login'      => ['index', 'auth', 'logout'],
            'register'   => ['index'],
            'recover'    => ['index'],
            'vote'       => ['index', 'verify'],
            'errors'     => ['show401', 'show404', 'show500'],
            'pages'      => ['docs', 'advertising', 'premium', 'faq'],
            'premium'    => ['index']
        ];

        $private = [
            'logout'  => ['index'],
            'profile' => ['index', 'add', 'edit'],
            'servers' => ['add', 'edit', 'delete'],
            'premium' => ['verify', 'process', 'paypal']
        ];

        $admin = [
            'admin'     => [
                'index', 'payments', 'users', 'products', 'categories', 'convert',
                'add', 'edit', 'delete', 'view', 'user', 'banlist'
            ]
        ];

        foreach ($public as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }

        foreach ($private as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }

        foreach ($admin as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }

        // grants all roles access to public areas
        foreach ($roles as $role) {
            foreach ($public as $resource => $actions) {
                foreach ($actions as $action){
                    $acl->allow($role->getName(), $resource, $action);
                }
            }
        }

        //Grant access to private area to role Users
        foreach ($private as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow('member', $resource, $action);
                $acl->allow('admin', $resource, $action);
            }
        }

        //Grant access to admin area to role Admins
        foreach ($admin as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow('admin', $resource, $action);
            }
        }

        return $acl;
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
        $role       = "guest";

        if ($controller == 'api') {
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
            return true;
        }

        if ($this->session->has('access_token')) {
            if ($this->dispatcher->getControllerName() == "login") {
                $this->response->redirect("");
                return false;
            }

            $userInfo = $this->session->get("user_info");

            $user = Users::getUser($userInfo->id);

            if (!$user) {
                $this->session->destroy();
                return $this->response->redirect("");
            }

            $role = $user->getRole();
            $this->view->user = $userInfo;
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
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'show401'
            ]);
            return false;
        }

        return true;
    }

}
