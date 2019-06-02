<?php
class IndexController extends BaseController {

    public function indexAction() {
        
    }

    public function logoutAction() {
        if (!$this->session->has("access_token")) {
            return $this->response->redirect("");
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
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show500'
            ]);
            $this->view->message = $userInfo['message'];
            return false;
        }

        $this->session->remove("access_token");
        return $this->response->redirect("");
    }

}