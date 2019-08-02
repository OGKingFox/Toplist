<?php

class PagesController extends BaseController {

    public function docsAction() {
        $this->tag->setTitle(ucwords($this->router->getActionName()));
    }

}