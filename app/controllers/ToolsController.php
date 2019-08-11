<?php

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray;

class ToolsController extends BaseController {

    public function itemsAction($page = 1) {
        $dir  = dirname(__FILE__);
        $data = $this->getList('items-complete');

        $itemList = (new NativeArray([
            'data'  => $data,
            'limit' => 50,
            'page'  => $page
        ]))->getPaginate();

        $this->debug($itemList);
    }

    public function searchAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);

        $data   = json_decode($this->getList('items-complete'), true);
        $search = $this->request->getPost("search", "string");
        $found  = [];

        if ($search != null) {
            foreach ($data as $key => $value) {
                $itemName = $value['name'];
                if (stripos(strtolower($itemName), strtolower($search)) !== false) {
                    $found[] = $value;
                }
            }
        }

        $itemList = (new NativeArray([
            'data'  => $found,
            'limit' => 50,
            'page'  => $this->dispatcher->getPost("page", "int", 1)
        ]))->getPaginate();
    }

    private function getList($name) {
        $cache    = new BackFile(new FrontData(['lifetime' => 1440 ]), [ 'cacheDir' =>  "../app/compiled/" ]);
        $itemList = $cache->get("$name.cache");

        if (!$itemList) {
            $itemList = json_decode(file_get_contents("../resources/$name.json"), true);
            $cache->save("$name.cache", $itemList);
        }

        return $itemList;
    }

}