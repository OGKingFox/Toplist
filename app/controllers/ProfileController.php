<?php
use \Phalcon\Text;

class ProfileController extends BaseController {

    public function indexAction() {
        $userInfo = $this->session->get("user");
        $servers = Servers::getServersByOwner($userInfo->id);

        $this->view->servers = $servers;
    }

    public function settingsAction() {
        if ($this->request->isPost() && $this->request->hasPost("theme_id")) {
            $user_id = $this->getUser()->id;
            $user    = Users::getUser($user_id);

            if (!$user) {
                $this->response->redirect("profile");
                return false;
            }

            $theme_id = $this->request->getPost("theme_id", "string");

            if ($theme_id == -1) {
                $user->setThemeId(null);
                $user->update();
                return $this->response->redirect("profile/settings");
            } else {
                $th = ThemeHandler::getInstance();

                if ($th->themeExists($theme_id)) {
                    $user->setThemeId($theme_id);
                    $user->update();
                    return $this->response->redirect("profile/settings");
                }

                $this->flash->error("Invalid theme file.");
            }
        }

        $this->view->themes = $this->getThemes();
        return true;
    }

}