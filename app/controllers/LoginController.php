<?php

use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\View;

class LoginController extends BaseController {

    public function indexAction() {
        global $config;

        $params = array(
            'client_id'     => $config->path("discord.oauth.client_id"),
            'redirect_uri'  => $config->path("discord.oauth.redirect_uri"),
            'response_type' => 'code',
            'scope'         => 'identify guilds email'
        );

        return $this->response->redirect('https://discordapp.com/api/oauth2/authorize?'.http_build_query($params));
    }

    public function urlAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        global $config;

        if (!$this->request->isAjax()) {
            return false;
        }

        $params = array(
            'client_id'     => $config->path("discord.oauth.client_id"),
            'redirect_uri'  => $config->path("discord.oauth.redirect_uri"),
            'response_type' => 'code',
            'scope'         => 'identify guilds email'
        );

        echo 'https://discordapp.com/api/oauth2/authorize?'.http_build_query($params);
        return false;
    }

    public function authAction() {
        global $config;

        if (!$this->request->hasQuery("code")) {
            return $this->response->redirect("");
        }

        $response = (new NexusBot())
            ->setEndpoint("oauth2/token")
            ->setType("post")
            ->setContentType("x-www-form-urlencoded")
            ->setData([
                "grant_type"    => "authorization_code",
                'client_id'     => $config->path("discord.oauth.client_id"),
                'client_secret' => $config->path("discord.oauth.client_secret"),
                'redirect_uri'  => $config->path("discord.oauth.redirect_uri"),
                'code'          => $this->request->getQuery("code")
            ])
            ->submit();

        if (isset($response->access_token)) {
            $expires = $response->expires_in;
            $token   = $response->access_token;

            $userInfo = (new NexusBot())
                ->setEndpoint("users/@me")
                ->setAccessToken($token)
                ->submit();

            if (!$userInfo || isset($userInfo->code)) {
                $this->logout();
                $this->response->redirect("");
                return false;
            }

            $user = Users::getUser($userInfo->id);

            if (!$user) {
                $user = new Users;
            }

            $user->setUserId($userInfo->id);
            $user->setEmail($userInfo->email);
            $user->setUsername($userInfo->username);
            $user->setDiscriminator($userInfo->discriminator);
            $user->setAvatar($userInfo->avatar);

            $server_id = $config->path("discord.server_id");

            $server_info = (new NexusBot())
                ->setIsBot(true)
                ->setEndpoint("guilds/".$server_id."/members/".$userInfo->id)
                ->submit();

            if (!$server_info || isset($server_info->code)) {
                $user->setRole("Member");
                $user->save();

                $this->cookies->set("access_token", $token, time() + $expires, $config->path("core.base_url"));
                $this->session->set("user", $userInfo);
                return $this->response->redirect("");
            }

            $user_roles = $server_info->roles;

            $roles = [
                '569975210409984009' => 'Owner',
                '569975354941374464' => 'Administrator',
                '569975599867625487' => 'Moderator',
                '607355749676351491' => 'Server Owner',
                '198682930338463744' => 'Member'
            ];

            $keys = array_keys($roles);
            $role = "Member";

            foreach ($keys as $key) {
                if (in_array($key, $user_roles)) {
                    $role = $roles[$key];
                    break;
                }
            }

            $user->setRole($role);
            $user->save();

            $this->cookies->set("access_token", $token, time() + $expires, $config->path("core.base_url"));
            $this->session->set("user", $userInfo);
        }

        return $this->response->redirect("");
    }

}