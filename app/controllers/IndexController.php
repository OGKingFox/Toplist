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

    public function logoutAction() {
        $this->cookies->set("access_token", '', time() - 1000, base_url);
        $this->session->destroy();
        return $this->response->redirect("");
    }

}