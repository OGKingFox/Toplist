<?php

use Phalcon\Mvc\View;

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
                    $expires = $user->getPremiumExpires();
                    $user->setPremiumExpires(($expires ? $expires : time()) + $package->getLength());

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
    }

    public function newsAction($page = 1) {


        $article = Articles::getArticles();
        $this->view->articles = $article;
    }

    public function newspostAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        $type = $this->request->getPost("type");
        $id   = $this->request->getPost("id", "int");

        if ($type == "add") {
            $article = new Articles;
            $article->assign($this->request->getPost());
            $article->setUserId($this->getUser()->id);
            $article->setDatePosted(time());

            if (!$article->save()) {
                $this->printStatus(false, 'Article failed to save: '.$article->getMessages()[0]);
                return false;
            }

            $this->printStatus(true, 'Article created!');
            return true;
        } elseif ($type == "edit") {
            $article = Articles::getArticle($id);
            $article->assign($this->request->getPost());

            if (!$article->update()) {
                $this->printStatus(false, 'Article failed to update: '.$article->getMessages()[0]);
                return false;
            }

            $this->printStatus(true, 'Article updated!');
            return true;
        } else if ($type == "delete") {
            $article = Articles::getArticle($id);
            if (!$article->delete()) {
                $this->printStatus(true, "Error deleting article.");
                return false;
            }
            $this->printStatus(true, "Article deleted!");
            return false;
        } else if ($type == "form") {
            $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
            $this->view->pick("dashboard/news/edit_form");
            $this->view->article = Articles::getArticle($id);
            return true;
        }

        return true;
    }

    public function usersAction() {
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
                    $expires = $user->getPremiumExpires();
                    $user->setPremiumExpires(($expires ? $expires : time()) + $package->getLength());

                    if ($package->id > $user->getPremiumLevel()) {
                        $user->setPremiumLevel($package->id);
                    }

                    if ($user->update()) {
                        $this->flash->success("{$user->getUsername()} has been given {$package->getTitle()}");
                    } else {
                        $this->flash->error("An error occurred: " . $user->getMessages()[0]);
                    }
                }
            }
        }
        $this->view->packages = Packages::find();
    }

    public function premiumAction() {

    }
}