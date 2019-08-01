<?php
use \Phalcon\Mvc\Controller;
use \Phalcon\Mvc\Dispatcher;

class BaseController extends Controller {

    public function printData($data) {
        echo "<pre>".json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES)."</pre>";
    }

    public function tidyText($text) {
        $tidyConfig = [
            'clean'          => true,
            'output-xhtml'   => true,
            'show-body-only' => true,
            'wrap'           => 0,
        ];

        $tidy = new Tidy;
        $tidy->parseString($text, $tidyConfig, 'utf8');
        $tidy->cleanRepair();

        return $tidy;
    }
    public function logout() {
        if (!$this->session->has("access_token")) {
            return false;
        }

        $userInfo = (new RestClient())
            ->setEndpoint("oauth2/token/revoke")
            ->setType('post')
            ->setContentType("x-www-form-urlencoded")
            ->setData([
                "token"         => $this->session->get("access_token"),
                'client_id'     => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
            ])
            ->setUseKey(false)
            ->submit(true);

        if (isset($userInfo['code']) && $userInfo['code'] == 0) {
            return false;
        }

        $this->session->destroy();
        return true;
    }

    public function getUser() {
        return $this->session->get("user_info");
    }

    public function getUserAvatar() {
        $user    = $this->getUser();
        $user_id = $user->id;
        $hash    = $user->avatar;
        $isGif    = substr($hash, 0, 2) == "a_";

        $base_url   = "https://cdn.discordapp.com/avatars/";
        return $base_url.$user_id.'/'.$hash.'.'.($isGif ? 'gif' : 'png').'';
    }

    function getRealIp(){
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @param $msg array
     */
    public function println($msg) {
        echo json_encode($msg, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $msg array
     */
    public function debug($msg) {
        echo "<pre>".json_encode($msg, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."</pre>";
    }


    /**
     * get access token from header
     */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Gets the authorization header.
     * @return string|null
     */
    public function getAuthorizationHeader() {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
}