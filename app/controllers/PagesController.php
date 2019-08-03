<?php

class PagesController extends BaseController {


    public function docsAction() {

    }

    public function premiumAction() {
        $packages = Packages::find();
        $this->view->packages = $packages;
    }

    public function faqAction() {

    }


}