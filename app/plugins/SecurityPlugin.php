<?php
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Mvc\View;

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
            'index'      => ['index', 'logout', 'discord'],
            'servers'    => ['index', 'view', 'stats', 'discord', 'report', 'like'],
            'login'      => ['index', 'auth', 'logout', 'url'],
            'register'   => ['index'],
            'recover'    => ['index'],
            'vote'       => ['index', 'verify'],
            'errors'     => ['show401', 'show404', 'show500'],
            'pages'      => ['docs', 'advertising', 'faq', 'terms', 'privacy', 'hosting'],
            'premium'    => ['index'],
            'tools'      => ['index', 'items', 'search']
        ];

        $private = [
            'logout'  => ['index'],
            'profile' => ['index', 'add', 'edit', 'settings'],
            'servers' => ['add', 'edit', 'delete', 'banner', 'images', 'removeimage'],
            'premium' => ['verify', 'process', 'button']
        ];

        $admin = [
            'dashboard'     => [
                'index', 'users', 'premium', 'servers', 'payments', 'reports', 'news', 'newspost', 'graph',
                'themes'
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
            $this->view->setRenderLevel(View::LEVEL_NO_RENDER);
            return true;
        }

        $omit = [
            'index' => ['stats']
        ];

        if (in_array($controller, array_keys($omit)) && in_array($action, $omit[$controller])) {
            return true;
        }

        global $config;
        $base_url = $config->path("core.base_url");

        if ($this->cookies->has('access_token')) {
            $access_token = $this->cookies->get("access_token");
            $verified     = $this->verifyUser($access_token);

            if (!$verified) {
                $this->cookies->set("access_token", '', time() - 1000, $base_url);
                $this->session->destroy();
                $this->response->redirect("");
                return false;
            }

            $user_id = $verified->id;
            $user = Users::getUser($user_id);

            if (!$user) {
                $this->cookies->set("access_token", '', time() - 1000, $base_url);
                $this->session->destroy();
                $this->response->redirect("");
                return false;
            }

            if (!$this->session->has("user")) {
                $this->session->set("user", $verified);
            }

            if ($user->getThemeId() != null) {
                $th = ThemeHandler::getInstance();

                if ($th->themeExists($user->getThemeId())) {
                    $this->view->user_theme = $user->getThemeId();
                } else {
                    $user->setThemeId(null);
                    $user->update();
                }
            }

            $role = $user->getRole();

            $this->view->user = $user;
            $this->view->role = strtolower($role);
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

    private function verifyUser($access_token) {
        $userInfo = (new NexusBot())
            ->setEndpoint("users/@me")
            ->setAccessToken($access_token)
            ->submit();

        if (!$userInfo || isset($userInfo->code)) {
            return false;
        }
        return $userInfo;
    }

}
