<?php
/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 6/2/19
 * Time: 12:46 AM
 */

class CreateController extends BaseController {

    public function indexAction() {
        if ($this->request->isPost() && $this->security->checkToken()) {
            $owner  = $this->session->get("user_info");
            $server = new Servers($this->request->getPost());

            $server->setOwnerId($owner->id);
            $server->setOwnerTag($owner->username.'#'.$owner->discriminator);

            if (!$server->save()) {
                $this->flash->error($server->getMessages());
            } else {
                return $this->response->redirect("server/".$server->getId());

                $this->flash->success("Your server has been created!");
            }
        }

        $this->view->games = Games::find();
    }
}