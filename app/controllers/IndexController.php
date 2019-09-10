<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Tag;

class IndexController extends BaseController {

    public function indexAction() {
        if ($this->request->hasQuery("darkMode")) {
            if ($this->cookies->has("darkMode")) {
                $this->cookies->set("darkMode", null, time() - 1000, '/');
            } else {
                $this->cookies->set("darkMode", 1, time() + (86400 * 30), '/');
            }
            return $this->response->redirect($this->request->getHTTPReferer());
        }

        $path  = $this->getConfig()->path("core.base_path");
        $cache = new BackFile(new FrontData(), ['cacheDir' => $path."/app/compiled/"]);
        $data  = $cache->get('home.stats.cache', 600);

        if (!$data) {
            $data = [
                'users'   => Users::count(),
                'servers' => Servers::count(),
                'votes'   => Votes::count(),
                'likes'   => Likes::count(),
            ];

            $cache->save("home.stats.cache", $data);
        }

        $articles = Articles::getArticles();

        $this->view->users    = $data['users'];
        $this->view->servers  = $data['servers'];
        $this->view->votes    = $data['votes'];
        $this->view->likes    = $data['likes'];
        $this->view->articles = $articles;
    }

    public function logoutAction() {
        $config = $this->getConfig();
        $this->cookies->set("access_token", '', time() - 1000, $config->path("core.base_url"));
        $this->session->destroy();
        return $this->response->redirect("");
    }

}