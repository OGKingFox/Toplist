<?php
class IndexController extends BaseController {

    public function indexAction() {
        
    }

    public function logoutAction() {
        $this->session->destroy();
        return $this->response->redirect("");
    }

}