<?php
use \Phalcon\Text;

class ProfileController extends BaseController {

    public function indexAction() {

    }

    public function addAction() {
        $this->tag->setTitle("Add Server");

        if ($this->request->isPost() /*&& $this->security->checkToken()*/) {
            $owner  = $this->session->get("user_info");
            $server = new Servers($this->request->getPost());

            $server->setOwnerId($owner->id);
            $server->setOwnerTag($owner->username.'#'.$owner->discriminator);
            $server->setInfo(Functions::getPurifier()->purify($server->getInfo()));
            $server->setDateCreated(time());
            $server->setApiKey(Text::random(Text::RANDOM_ALNUM, 15));

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
        $this->tag->setTitle("Edit Server");

        $owner  = $this->session->get("user_info");
        $server = Servers::getServerByOwner($owner->id);

        if ($this->session->has("notice")) {
            $notice = $this->session->get("notice");
            $this->flash->message($notice['type'], $notice['message']);
            $this->session->remove('notice');
        }

        if ($this->request->isPost() /*&& $this->security->checkToken()*/) {
            $owner  = $this->session->get("user_info");

            $server->assign($this->request->getPost());
            $server->setOwnerTag($owner->username.'#'.$owner->discriminator);
            $server->setInfo(Functions::getPurifier()->purify($server->getInfo()));

            if (!$server->update()) {
                $this->flash->error($server->getMessages());
            } else {
                $this->session->set("notice", [
                    'type' => 'success',
                    'message' => 'Your changes have been saved.'
                ]);
                return $this->response->redirect("profile/edit");
            }
        }

        $this->view->games  = Games::find();
        $this->view->server = $server;
        return true;
    }
}