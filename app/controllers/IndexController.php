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

    public function viewAction($id, $title) {
        $id = $this->filter->sanitize($id, "int");

        $server = Servers::getServer($id);

        if (!$server) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        $votes    = Votes::getVoteTotalForMonth($server->id)->total;
        $voteData = Votes::getVotesForMonth($server->id);

        $this->view->votes     = $votes;
        $this->view->server    = $server;
        $this->view->voteData  = $voteData;
        $this->view->days      = range(1, date('t'));
        $this->view->seo_title = Servers::genSeoTitle($server);


        $resetsOn = date("Y-m-t 23:59:59");
        $future = new DateTime($resetsOn);
        $differ = $future->diff(new DateTime());

        $this->view->resetIn = $differ->format("%dd %hh %im %ss");
        return true;
    }

    public function logoutAction() {
        $this->logout();
        return $this->response->redirect("");
    }

}