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
            'owner'      => new Role('Owner'),
            'admins'     => new Role('Administrator'),
            'moderators' => new Role('Moderator'),
            'owners'     => new Role('Server Owner'),
            'members'    => new Role('Member'),
            'guests'     => new Role('guest')
        ];

        foreach ($roles as $role) {
            $acl->addRole($role);
        }

        $public = [
            'index'      => ['index', 'logout', 'view', 'like', 'report', 'stats', 'discord'],
            'login'      => ['index', 'auth', 'logout'],
            'register'   => ['index'],
            'recover'    => ['index'],
            'vote'       => ['index', 'verify'],
            'errors'     => ['show401', 'show404', 'show500'],
            'pages'      => ['docs', 'advertising', 'faq', 'terms', 'privacy'],
            'premium'    => ['index']
        ];

        $private = [
            'logout'  => ['index'],
            'profile' => ['index', 'add', 'edit'],
            'servers' => ['add', 'edit', 'delete', 'upload'],
            'premium' => ['verify', 'process', 'button']
        ];

        $admin = [
            'dashboard'     => [
                'index', 'users', 'premium', 'servers', 'payments', 'reports'
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
                $acl->allow('Member', $resource, $action);
                $acl->allow('Server Owner', $resource, $action);
                $acl->allow('Moderator', $resource, $action);
                $acl->allow('Administrator', $resource, $action);
                $acl->allow('Owner', $resource, $action);
            }
        }

        //Grant access to admin area to role Admins
        foreach ($admin as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow('Administrator', $resource, $action);
                $acl->allow('Owner', $resource, $action);
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

        $omit = [
            'index' => ['stats']
        ];

        if (in_array($controller, array_keys($omit)) && in_array($action, $omit[$controller])) {
            return true;
        }

        if ($this->cookies->has('access_token')) {
            if ($this->dispatcher->getControllerName() == "login") {
                $this->response->redirect("");
                return false;
            }

            $access_token = $this->cookies->get("access_token");
            $verified     = $this->verifyUser($access_token);

            echo json_encode($verified);

            if (!$verified) {
                $this->session->destroy();
                $this->response->redirect("");
                return false;
            }

            $user = Users::getUser($verified->id);

            if (!$user) {
                $this->logout();
                $this->response->redirect("");
                return false;
            }

            if (!$this->session->has("user")) {
                $this->session->set("user", $verified);
            }

            $role = $user->getRole();

            $this->view->user    = $user;
            $this->view->role    = strtolower($role);
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

    public function logout() {
        if (!$this->cookies->has("access_token")) {
            return false;
        }

        $userInfo = (new RestClient())
            ->setEndpoint("oauth2/token/revoke")
            ->setType('post')
            ->setContentType("x-www-form-urlencoded")
            ->setData([
                "token"         => $this->cookies->get("access_token"),
                'client_id'     => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
            ])
            ->setUseKey(false)
            ->submit(true);

        if (isset($userInfo['code']) && $userInfo['code'] == 0) {
            return false;
        }

        $this->cookies->reset();
        $this->session->destroy();
        return true;
    }

    private function verifyUser($access_token) {
        $userInfo = (new RestClient())
            ->setEndpoint("users/@me")
            ->setAccessToken($access_token)
            ->setUseKey(true)
            ->submit();

        if (!$userInfo || isset($userInfo->code)) {
            return false;
        }
        return $userInfo;
    }

}
