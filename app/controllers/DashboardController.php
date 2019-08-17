<?php

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Tag;

class DashboardController extends BaseController {

    private $user;

    public function indexAction() {
        $this->view->users   = Users::count();
        $this->view->votes   = Votes::count();
        $this->view->likes   = Likes::count();
        $this->view->servers = Servers::count();

        $votes = $this->getGraphData(13);

        $this->view->days = array_column($votes, 'time');
        $this->view->data = array_column($votes, 'total');
    }

    /**
     * @param $days
     * @return array
     */
    public function getGraphData($days = 14) {
        $cache = new BackFile(new FrontData(), ['cacheDir' => "../app/compiled/servers/statistics/"]);
        $data  = $cache->get('global.cache', 600);

        if (!$data) {
            $timeInSecs = (60 * 60 * 24 * $days);

            $data = Votes::query()
                ->columns([
                    "FROM_UNIXTIME(voted_on, '%m/%d') AS time",
                    'COUNT(*) AS total'
                ])
                ->conditions("UNIX_TIMESTAMP() - voted_on < $timeInSecs")
                ->groupBy("time")
                ->orderBy("ANY_VALUE(time) ASC")
                ->execute()->toArray();

            $cache->save('global.cache', $data);
        }
        return $data;
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

    public function usersAction($page = 1) {
        if ($this->request->isPost()
                && $this->request->hasPost("user_id")
                && $this->security->checkToken()) {
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

        if ($this->request->isPost()) {
            $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

            $id   = $this->request->getPost("id", "int");
            $type = $this->request->getPost("type", "string");
            $user = Users::getUser($id);

            switch ($type) {
                case "revoke":
                    $user->setPremiumLevel(0);
                    $user->setPremiumExpires(-1);
                    $user->update();
                    $this->printStatus(true, $user->getUsername().' no longer has premium.');
                    break;
                case "ban":
                    $ban = (new UserActions($user))->ban();

                    if ($ban['success']) {
                        (new BotMessage([
                            'channel' => '610038623743639559',
                            'title' => 'User Banned',
                            'message' => "<@{$this->getUser()->id}> has banned <@{$user->getUserId()}> from the server",
                            'is_rich' => true
                        ]))->send();
                    }

                    $this->println($ban);
                    break;
                case "unban":
                    $unban = (new UserActions($user))->unban();

                    if ($unban['success']) {
                        (new BotMessage([
                            'channel' => '610038623743639559',
                            'title' => 'User Unbanned',
                            'message' => "<@{$this->getUser()->id}> unbanned <@" . $user->getUserId() . ">.",
                            'is_rich' => true
                        ]))->send();
                    }

                    $this->println($unban);
                    break;
                case "kick":
                    $kick = (new UserActions($user))->kick();

                    if ($kick['success']) {
                        (new BotMessage([
                            'channel' => '514876434720882688',
                            'title' => 'User Kicked',
                            'message' => "<@{$this->getUser()->id}> has kicked <@{$user->getUserId()}> from the server.",
                            'is_rich' => true
                        ]))->send();
                    }

                    $this->println($kick);
                    break;
            }

            return true;
        }

        $users = Users::find();

        $paginator = (new \Phalcon\Paginator\Adapter\Model([
            'data' => $users,
            'limit' => 20,
            'page' => $page
        ]))->getPaginate();

        $this->view->users    = $paginator;
        $this->view->packages = Packages::find();
        return true;
    }

    public function premiumAction() {

    }
}