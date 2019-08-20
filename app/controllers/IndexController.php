<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Tag;

class IndexController extends BaseController {

    public function indexAction() {
       // $this->view->articles = Articles::getArticles();

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
        $this->view->users    = Users::count();
        $this->view->servers  = Servers::count();
        $this->view->votes    = Votes::count();
        $this->view->likes    = Likes::count();
    }

    public function logoutAction() {
        $config = $this->getConfig();
        $this->cookies->set("access_token", '', time() - 1000, $config->path("core.base_url"));
        $this->session->destroy();
        return $this->response->redirect("");
    }

}