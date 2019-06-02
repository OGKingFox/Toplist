<?php

class LoginController extends BaseController {

    public function indexAction() {
        $params = array(
            'client_id'     => OAUTH2_CLIENT_ID,
            'redirect_uri'  => 'http://localhost/toplist/login/auth',
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
                'redirect_uri'  => 'http://localhost/toplist/login/auth',
                'code'          => $this->request->getQuery("code")
            ])
            ->submit();

        echo "<pre>".json_encode($response, JSON_PRETTY_PRINT)."</pre>";

        if ($response->access_token) {
            $token = $response->access_token;
            $this->session->set("access_token", $token);
            return $this->response->redirect("");
        }

        return $this->response->redirect("");
    }

}