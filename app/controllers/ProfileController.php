<?php
use \Phalcon\Text;

class ProfileController extends BaseController {

    public function indexAction() {
        $userInfo = $this->session->get("user");
        $this->view->servers = Servers::getServersByOwner($userInfo->id);
    }

    public function settingsAction() {
        if ($this->request->isPost() && $this->request->hasPost("theme_id")) {
            $user_id = $this->getUser()->id;
            $user    = Users::getUser($user_id);

            if (!$user) {
                $this->response->redirect("profile");
                return false;
            }

            $theme_id = $this->request->getPost("theme_id", "int");

            if ($theme_id == -1) {
                $user->setThemeId(-1);
                $user->update();
                return $this->response->redirect("profile/settings");
            } else {
                $theme = Themes::getTheme($theme_id);

                if (!$theme) {
                    $this->flash->error("Invalid theme id.");
                } else {
                    $user->setThemeId($theme->getId());
                    $user->update();
                    return $this->response->redirect("profile/settings");
                }
            }

            $this->debug($this->getUser());
        }

        $this->view->themes = Themes::getThemes();
        return true;
    }

}