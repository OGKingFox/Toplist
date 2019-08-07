<?php
use \Phalcon\Text;

class ProfileController extends BaseController {

    public function indexAction() {
        $userInfo = $this->session->get("user");
        $this->view->servers = Servers::getServersByOwner($userInfo->id);
    }
}