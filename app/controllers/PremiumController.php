<?php

class PremiumController extends BaseController {

    private static $enabled = true;

    public function indexAction() {
        $packages = Packages::find();
        $this->view->packages = $packages;
    }

    public function verifyAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);

        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->printStatus(false, "This page is only available via a post request.");
            return false;
        }

        if (!self::$enabled) {
            $this->printStatus(false, "Purchasing premium is currently disabled for maintenance.");
            return false;
        }

        $data = $this->request->getPost("postdata");

        if (empty($data)) {
            $this->printStatus(false, "We could not process your payment due to not receiving any data.");
            return false;
        }

        $buyer   = $data['payer']['payer_info'];
        $user_id = $data['transactions'][0]['custom'];

        $user = Users::getUser($user_id);

        if (!$user) {
            $this->printStatus(false, "Could not find your account. You have not been charged.");
            return false;
        }

        $item   = $data['transactions'][0]['item_list']['items'][0];
        $name   = $item['name'];
        $id     = $item['sku'];
        $paid   = $item['price'];

        $package = Packages::getPackage($id);

        if (!$package) {
            $this->printStatus(false, "Purchase failed. Premium package could not be loaded.");
            return false;
        }

        if (number_format($paid, 2) != number_format($package->price, 2)) {
            $this->printStatus(false, "Purchase failed. Price has somehow been changed?");
            return false;
        }

        $this->printStatus(true, "Purchase is valid.");
        return true;
    }

    public function processAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);

        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->printStatus(false, "This page is only available via a post request.");
            return false;
        }

        if (!self::$enabled) {
            $this->printStatus(false, "Purchasing premium is currently disabled for maintenance.");
            return false;
        }

        $data = $this->request->getPost("postdata");

        if (empty($data)) {
            $this->printStatus(false, "We could not process your payment due to not receiving any data.");
            return false;
        }

        $buyer    = $data['payer']['payer_info'];
        $trans    = $data['transactions'][0];
        $res      = $trans['related_resources'][0]['sale'];

        $user_id = $trans['custom'];
        $user    = Users::getUser($user_id);

        if (!$user) {
            $this->printStatus(false, "Could not find your account. You have not been charged.");
            return false;
        }

        $transId  = $this->filter->sanitize($res['id'], "string");
        $state    = $this->filter->sanitize($res['state'], "string");

        $item     = $trans['item_list']['items'][0];
        $id       = $item['sku'];
        $paid     = $item['price'];

        $package = Packages::getPackage($id);

        if (!$package) {
            $this->printStatus(false, "Purchase failed. Premium package could not be loaded.");
            return false;
        }

        if (number_format($paid, 2) != number_format($package->price, 2)) {
            $this->printStatus(false, "Purchase failed. Price has somehow been changed?");
            return false;
        }

        $user->setPremiumLevel($package->id);
        $user->setPremiumExpires(time() + $package->length);

        if (!$user->update()) {
            $this->printStatus(false, "Failed to apply premium: ".$user->getMessages()[0]);
            return false;
        }

        $this->printStatus(true, "Thank you! {$package->title} has been applied to your account.");
    }

    public function buttonAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);

        if (!$this->request->isPost() || !$this->request->isAjax() || !$this->request->hasPost("pid")) {
            $this->printStatus(false, 'This page is available via post only.');
            return false;
        }

        if (!self::$enabled) {
            $this->printStatus(false, "Purchasing premium is currently disabled for maintenance.");
            return false;
        }

        $this->view->package = Packages::getPackage($this->request->getPost("pid", "int"));
        return true;
    }

}