<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class IndexController extends BaseController {

    public function indexAction($gameId = 1) {
        $gameId = $this->filter->sanitize($gameId, is_numeric($gameId) ? 'int' : 'string');
        $game = Games::getGameByIdOrName($gameId);

        if (!$game) {
            return $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
        }

        $servers = Servers::getServers($game ? $game->getId() : null);

        //$this->debug($servers);

        $this->view->game     = $game;
        $this->view->games    = Games::find();
        $this->view->servers  = Servers::getServers($game ? $game->getId() : null);
        $this->view->myServer = Servers::getServerByOwner($this->getUser()->id);
        return true;
    }

    public function viewAction($id) {
        $id = $this->filter->sanitize($id, "int");

        $server = Servers::getServer($id);

        if (!$server) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        if ($this->request->isPost() && $this->security->checkToken()) {
            $user_id    = $this->getUser()->id;
            $username   = $this->getUser()->username;

            $body = $this->request->getPost("comment", ['string', 'trim']);

            $comment = (new Comments)
                ->setServerId($server->id)
                ->setUserId($user_id)
                ->setUsername($username)
                ->setComment($body)
                ->setDatePosted(time());

            if (!$comment->save()) {
                $this->flash->error("Could not save comment: ".$comment->getMessages()[0]);
            } else {
                return $this->response->redirect('view/'.Servers::genSeoTitle($server));
            }
        }

        $seo    = Servers::genSeoTitle($server);
        $fCache = new FrontData(['lifetime' => '15']);
        $cache  = new BackFile($fCache, ['cacheDir' => "../app/compiled/servers/"]);
        $data   = $cache->get($seo.".cache");

        if (!$data) {
            $data = [
                'votes'    => Votes::getVoteTotalForMonth($server->id)->total,
                'voteData' => Votes::getVotesForMonth($server->id),
                'likes'    => Likes::getLikes($server->id)->amount,
            ];

            $cache->save($seo.".cache", $data);
        }

        $paginator = new PaginatorModel([
            'data'  => Comments::getComments($server->id),
            'limit' => 10,
            'page'  => $this->request->getQuery("page", "int", 1)
        ]);

        $this->view->votes     = $data['votes'];
        $this->view->server    = $server;
        $this->view->voteData  = $data['voteData'];
        $this->view->days      = range(1, date('t'));
        $this->view->seo_title = $seo;
        $this->view->likes     = $data['likes'];
        $this->view->comments  = $paginator->getPaginate();
        $this->view->resetIn   = Functions::timeLeft('Y-m-t 23:59:59', '%dd %hh %im %ss');
        return true;
    }

    public function likeAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);

        if (!$this->request->isAjax()) {
            $this->println([
                'success' => false,
                'message' => 'This page is available via ajax only!'
            ]);
            return false;
        }

        $serverId = $this->request->getPost("id", "int");
        $server   = Servers::getServer($serverId);

        if (!$server) {
            $this->println([
                'success' => false,
                'message' => 'This server does not exist.'
            ]);
            return false;
        }

        $user_id = $this->getUser()->id;
        $like    = Likes::getLike($server->id, $user_id);

        if ($like) {
            $this->println([
                'success' => false,
                'message' => 'You have already liked this server!'
            ]);
            return false;
        }

        $like = new Likes;
        $like->setServerId($server->id);
        $like->setUserId($user_id);

        if (!$like->save()) {
            $this->println([
                'success' => false,
                'message' => 'An error occurred: '.$like->getMessages()[0]
            ]);
            return true;
        }

        $this->println([
            'success' => true,
            'message' => 'Your like has been recorded. Thank you!',
            'count' => Likes::getLikes($server->id)
        ]);
        return true;
    }

    public function logoutAction() {
        $this->logout();
        return $this->response->redirect("");
    }

}