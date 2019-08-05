<?php
class DashboardController extends BaseController {

    public function indexAction() {
        $user = Users::getUser($this->getUser()->id);
        $this->view->user     = $user;
        $this->view->avatar   = Functions::getAvatarUrl($this->getUser()->id, $this->getUser()->avatar);
        $this->view->packages = Packages::find();
    }

}