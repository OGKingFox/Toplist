<?php
use \Phalcon\Text;

class ProfileController extends BaseController {

    public function indexAction() {
        $user = Users::getUser($this->getUser()->id);
        $servers = Servers::getServersByOwner($this->getUser()->id);

        $this->view->servers = $servers;
        $this->view->user    = $user;
        $this->view->avatar  = Functions::getAvatarUrl($this->getUser()->id, $this->getUser()->avatar);
    }
}