<?php
class DashboardController extends BaseController {

    private $user;

    public function indexAction() {
        $user = Users::getUser($this->getUser()->id);

        if ($this->request->isPost() && $this->security->checkToken()) {
            $username = $this->request->getPost("username", 'string');
            $pid  = $this->request->getPost("package", "int");

            $package = Packages::getPackage($pid);

            if (!$package) {
                $this->flash->error("Invalid package id.");
            } else {

            }
        }

        $this->view->user     = $user;
        $this->view->avatar   = Functions::getAvatarUrl($this->getUser()->id, $this->getUser()->avatar);
        $this->view->packages = Packages::find();
    }

    public function beforeExecuteRoute(\Phalcon\Dispatcher $dispatcher) {
        parent::beforeExecuteRoute($dispatcher);

        $this->user = Users::getUser($this->getUser()->id);
        $this->view->avatar   = Functions::getAvatarUrl($this->getUser()->id, $this->getUser()->avatar);
    }
}