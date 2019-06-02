<?php

class ProfileController extends \Phalcon\Mvc\Controller {

    public function indexAction() {

    }

    public function addAction() {
        if ($this->request->isPost() && $this->security->checkToken()) {
            $owner  = $this->session->get("user_info");
            $server = new Servers($this->request->getPost());

            $server->setOwnerId($owner->id);
            $server->setOwnerTag($owner->username.'#'.$owner->discriminator);
            $server->setInfo(Functions::getPurifier()->purify($server->getInfo()));

            if (!$server->save()) {
                $this->flash->error($server->getMessages());
            } else {
                return $this->response->redirect("view/".$server->getSeoTitle());
            }
        }

        $this->view->games = Games::find();
        return true;
    }

    public function editAction() {
        $owner  = $this->session->get("user_info");
        $server = Servers::getServerByOwner($owner->id);

        if ($this->request->isPost() && $this->security->checkToken()) {
            $owner  = $this->session->get("user_info");

            $server->assign($this->request->getPost());
            $server->setOwnerTag($owner->username.'#'.$owner->discriminator);
            $server->setInfo(Functions::getPurifier()->purify($server->getInfo()));

            if (!$server->update()) {
                $this->flash->error($server->getMessages());
            } else {
                $this->flash->success("Your listing has been updated!");
            }
        }

        $this->view->games  = Games::find();
        $this->view->server = $server;
        return true;
    }
}