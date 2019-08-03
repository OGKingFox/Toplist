<?php

class PremiumController extends BaseController {

    public function indexAction() {
        $packages = Packages::find();
        $this->view->packages = $packages;
    }

    public function verifyAction() {

    }

    public function processAction() {

    }

    public function paypalAction() {

    }

}