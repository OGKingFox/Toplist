<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Tag;

class IndexController extends BaseController {

    public function indexAction() {
        $articles = Articles::getArticles();

        $this->view->articles = $articles;

        $this->view->users   = Users::count();
        $this->view->servers = Servers::count();
        $this->view->votes   = Votes::count();
    }

    public function discordAction() {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        $cache  = new BackFile(new FrontData(['lifetime' => 600]), ['cacheDir' => "../app/compiled/"]);
        $data = $cache->get("rn.discord.cache");

        if (!$data) {
            $data = $this->getDiscordData();
            $cache->save("rn.discord.cache", $data);
        }

        $this->view->server  = $data['server'];
        $this->view->members = $data['members'];
        $this->view->invite  = $data['invite'];

       // $this->debug($data);
    }

    public function logoutAction() {
        $this->cookies->set("access_token", '', time() - 1000, base_url);
        $this->session->destroy();
        return $this->response->redirect("");
    }

}