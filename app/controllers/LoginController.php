<?php

use Phalcon\Mvc\ModelInterface;

class LoginController extends BaseController {

    public function indexAction() {
        $params = array(
            'client_id'     => OAUTH2_CLIENT_ID,
            'redirect_uri'  => redirect_uri,
            'response_type' => 'code',
            'scope'         => 'identify guilds email'
        );

        return $this->response->redirect('https://discordapp.com/api/oauth2/authorize?'.http_build_query($params));
    }

    public function authAction() {
        if (!$this->request->hasQuery("code")) {
            return $this->response->redirect("");
        }

        $response = (new RestClient())
            ->setEndpoint("oauth2/token")
            ->setType("post")
            ->setData([
                "grant_type"    => "authorization_code",
                'client_id'     => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
                'redirect_uri'  => redirect_uri,
                'code'          => $this->request->getQuery("code")
            ])
            ->submit();

        if (isset($response->access_token)) {
            $expires = $response->expires_in;
            $token   = $response->access_token;

            $userInfo = (new RestClient())
                ->setEndpoint("users/@me")
                ->setAccessToken($token)
                ->setUseKey(true)
                ->submit();

            $this->debug($userInfo);

            if (!$userInfo || isset($userInfo->code)) {
                $this->logout();
                $this->response->redirect("");
                return false;
            }

            if (!$user = Users::getUser($userInfo->id)) {
                $user = new Users;
            }

            $user->setUserId($userInfo->id);
            $user->setEmail($userInfo->email);
            $user->setUsername($userInfo->username);
            $user->setDiscriminator($userInfo->discriminator);
            $user->setAvatar($userInfo->avatar);

            $server_info = (new RestClient())
                ->setEndpoint("guilds/".server_id."/members/".$userInfo->id)
                ->submitBot(true);

            if (!$server_info || isset($server_info['code'])) {
                $user->setRole("member");
                $user->save();

                $this->cookies->set("access_token", $token, time() + $expires, base_url);
                $this->session->set("user", $userInfo);

                return $this->response->redirect("");
            }

            $user_roles = $server_info['roles'];

            $roles = [
                '569975210409984009' => 'Owner',
                '569975354941374464' => 'Administrator',
                '569975599867625487' => 'Moderator',
                '607355749676351491' => 'Server Owner',
                '198682930338463744' => 'Member'
            ];

            $keys = array_keys($roles);
            $role = "member";

            foreach ($keys as $key) {
                if (in_array($key, $user_roles)) {
                    $role = $roles[$key];
                    break;
                }
            }

            $user->setRole($role);
            $user->save();

            $this->cookies->set("access_token", $token, time() + $expires, base_url);
            $this->session->set("user", $userInfo);
        }

        return $this->response->redirect("");
    }

}