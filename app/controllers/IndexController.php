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
        $this->view->likes   = Likes::count();
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

        $this->debug($data);
    }

    public function logoutAction() {
        $this->cookies->set("access_token", '', time() - 1000, base_url);
        $this->session->destroy();
        return $this->response->redirect("");
    }

    function apiRequest($url, $token, $post=FALSE, $headers=array()) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: Bearer ' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        return json_decode($response);
    }

}