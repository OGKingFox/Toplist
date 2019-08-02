<?php

class PagesController extends BaseController {

    public function docsAction() {
        $this->tag->setTitle(ucwords($this->router->getActionName()));

        $this->view->description = "Documentation on how to get set up using our toplist to it's fullest, 
            including code examples and detailed guides.";
    }

}