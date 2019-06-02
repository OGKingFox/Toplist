<?php
class IndexController extends BaseController {

    public function indexAction() {
        $this->view->games = Games::find();
    }

    public function logoutAction() {
        $this->logout();
        return $this->response->redirect("");
    }

}