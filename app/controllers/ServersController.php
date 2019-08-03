<?php

use Phalcon\Mvc\View;
use Phalcon\Text;

class ServersController extends BaseController {

    public function addAction() {
        if ($this->request->isPost() /*&& $this->security->checkToken()*/) {
            $servers = Servers::query()
                    ->conditions('owner_id = :id:')
                    ->bind([
                        'id' => $this->getUser()->id
                    ])->execute();

            if (count($servers->toArray()) == 5) {
                $this->flash->error("You may only have up to 5 servers listed at a time.");
            } else {
                $server = new Servers($this->request->getPost());
                $server->setOwnerId($this->getUser()->id);
                $server->setOwnerTag($this->getUser()->username.'#'.$this->getUser()->discriminator);
                $server->setInfo(Functions::getPurifier()->purify($server->getInfo()));
                $server->setDateCreated(time());
                $server->setApiKey(Text::random(Text::RANDOM_ALNUM, 15));

                if (!$server->save()) {
                    $this->flash->error($server->getMessages());
                } else {
                    if ($server->getWebsite()) {
                        $seo  = Servers::genSeoTitle($server);

                        $bot = new NexusBot();
                        $bot->setMessage("{$this->getUser()->username}, has listed a new server: [{$server->getTitle()}](http://rune-nexus.com/view/{$seo})");
                        $bot->send();
                    }
                    return $this->response->redirect("view/".$server->getSeoTitle());
                }
            }
        }

        $this->view->games = Games::find();
        return true;
    }

    public function editAction($id = null) {
        if ($id == null || !is_numeric($id)) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        $server = Servers::getServerByOwner($id, $this->getUser()->id);

        if (!$server) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

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
                return $this->response->redirect("servers/edit/".$server->getId());
            }
        }

        $this->view->games  = Games::find();
        $this->view->server = $server;
        return true;
    }

    public function deleteAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        if (!$this->request->isPost() || !$this->request->isAjax() || !$this->request->hasPost("id")) {
            $this->printStatus(false, 'This page is available via post only');
            return false;
        }

        $id = $this->request->getPost("id", 'int');
        $server = Servers::getServerByOwner($id, $this->getUser()->id);

        if (!$server) {
            $this->printStatus(false, 'Could not find this server to remove it!');
            return false;
        }

        $server->delete();
        $this->printStatus(true, 'This server has been removed.');
        return true;
    }

    public function uploadAction() {

    }

}