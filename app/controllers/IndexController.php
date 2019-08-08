<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Tag;

class IndexController extends BaseController {

    public function indexAction() {

    }

    public function logoutAction() {
        $this->cookies->set("access_token", '', time() - 1000, base_url);
        $this->session->destroy();
        return $this->response->redirect("");
    }

}