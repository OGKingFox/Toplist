<?php
class IndexController extends BaseController {

    public function indexAction() {
        
    }

    public function logoutAction() {
        $this->logout();
        return $this->response->redirect("");
    }

}