<?php
class DashboardController extends BaseController {

    private $user;

    public function indexAction() {
        if ($this->request->isPost() && $this->security->checkToken()) {
            $user_id = $this->request->getPost("user_id", 'string');
            $pid = $this->request->getPost("package", "int");

            $package = Packages::getPackage($pid);

            if (!$package) {
                $this->flash->error("Invalid package id.");
            } else {
                $user = Users::getUser($user_id);

                if (!$user) {
                    $this->flash->error("User '$user_id' could not be found.");
                } else {
                    $user->setPremiumExpires($user->getPremiumExpires() + $package->getLength());
                    if ($package->id > $user->getPremiumLevel()) {
                        $user->setPremiumLevel($package->id);
                    }
                    if ($user->update()) {
                        $this->flash->success("{$user->getUsername()} has been given {$package->getTitle()}");
                    } else {
                        $this->flash->error("An error occurred: ".$user->getMessages()[0]);
                    }
                }
            }
        }

        $this->view->packages = Packages::find();
        $this->view->servers  = Servers::count();
        $this->view->votes    = Votes::count();
        $this->view->users    = Users::count();
        $this->view->likes    = Likes::count();
    }

}