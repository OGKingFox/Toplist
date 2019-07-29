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

    /**
     * @param $msg array
     */
    public function println($msg) {
        echo json_encode($msg);
    }

    /**
     * @param $msg array
     */
    public function debug($msg) {
        echo "<pre>".json_encode($msg, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."</pre>";
    }
}