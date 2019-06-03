<?php
class IndexController extends BaseController {

    public function indexAction($gameId = null) {
        $game = null;

        if ($gameId != null) {
            $parts = explode('-', $gameId);

            if (count($parts) > 0) {
                $gameId = $this->filter->sanitize($parts[0], "int");
                $game   = Games::getGameById($gameId);

                if (!$game) {
                    return $this->dispatcher->forward([
                        'controller' => 'errors',
                        'action' => 'show404'
                    ]);
                }

                $this->view->game = $game;
            }
        }

        $this->view->games    = Games::find();
        $this->view->servers  = Servers::getServers($game ? $game->getId() : null);
        $this->view->myServer = Servers::getServerByOwner($this->getUser()->id);
        return true;
    }

    public function logoutAction() {
        $this->logout();
        return $this->response->redirect("");
    }

}