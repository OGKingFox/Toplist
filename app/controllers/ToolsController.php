<?php

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\NativeArray;

class ToolsController extends BaseController {

    public function indexAction() {
        $this->updateItems();
    }

    public function itemsAction() {

    }

    public function searchAction() {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        $data   = $this->getItemList();
        $search = $this->request->getPost("search", "string");
        $found  = [];

        if ($search != null && $search != '') {
            foreach ($data as $item) {
                $itemName = $item['name'];
                if (stripos(strtolower($itemName), strtolower($search)) !== false) {
                    $found[] = $item;
                }
            }
        } else {
            $found = $data;
        }

        $itemList = (new NativeArray([
            'data'  => $found,
            'limit' => 50,
            'page'  => $this->request->getPost("page", "int", 1)
        ]))->getPaginate();

        $this->view->icon_url = 'https://www.osrsbox.com/osrsbox-db/items-icons/';
        $this->view->itemList = $itemList;
    }

    private function getItemList() {
        $cache = new BackFile(new FrontData(['lifetime' => 86400 ]), [
            'cacheDir' =>  "../app/compiled/"
        ]);

        $itemList = $cache->get("items.data.cache");

        if (!$itemList) {
            $itemList = $this->updateItems();
            $cache->save("items.data.cache", $itemList);
        }

        return $itemList;
    }

    /**
     * Grabs new items from OSRSBOX if the cache is expired. Falls back on the saved json file if fails.
     * @return array|mixed
     */
    private function updateItems() {
        $url  = "https://www.osrsbox.com/osrsbox-db/items-summary.json";
        $data = file_get_contents($url);

        if (!$data) {
            $data = file_get_contents("../resources/item-data.json");
        } else {
            $oldData = json_decode($data, true);
            $data    = [];

            foreach ($oldData as $key => $value) {
                $itemId   = $value['id'];
                $itemName = $value['name'];
                $data[] = ['id' => $itemId, 'name' => $itemName];
            }

            file_put_contents('../resources/item-data.json', json_encode($data));
        }
        return $data;
    }

}