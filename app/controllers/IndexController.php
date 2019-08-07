<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class IndexController extends BaseController {

    public function indexAction($gameId = 1) {
        $this->tag->setTitle("Home");

        $gameId = $this->filter->sanitize($gameId, is_numeric($gameId) ? 'int' : 'string');
        $game   = Games::getGameByIdOrName($gameId);

        if (!$game) {
            return $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
        }

        $serverList = (new PaginatorModel([
            'data'  => Servers::getServers($game ? $game->getId() : null),
            'limit' => 30,
            'page'  => $this->dispatcher->getParam("page", "int", 1)
        ]))->getPaginate();

        $this->view->game      = $game;
        $this->view->games     = Games::find();
        $this->view->servers   = $serverList;
        return true;
    }

    public function statsAction() {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        if (!$this->request->isAjax()) {
            return false;
        }

        $this->view->mostLiked = Likes::getMostLiked();
        $this->view->newest    = Servers::getNewestServers();
        $this->view->mostVotes = Servers::getMostVotedOn();
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

        $this->tag->setTitle($server->title);

        if ($server->meta_info) {
            $this->view->description = $server->meta_info;
        }
        if ($server->meta_tags) {
            $tags = implode(",", json_decode($server->meta_tags, true));
            $this->view->meta_tags = $this->filter->sanitize($tags, 'string');
        }

        if ($this->request->isPost() && $this->security->checkToken()) {
            $user_id  = $this->getUser()->id;
            $username = $this->getUser()->username;
            $type     = $this->request->getPost("type", 'string');

            if ($type == "comment") {
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
        }

        $seo    = Servers::genSeoTitle($server);

        $fCache = new FrontData(['lifetime' => '15']);
        $cache  = new BackFile($fCache, ['cacheDir' => "../app/compiled/servers/"]);
        $data   = $cache->get($seo.".cache");

        if (!$data) {
            $data = [
                'voteData' => Votes::getVotesForMonth($server),
                'likes'    => Likes::getLikes($server->id)->amount,
            ];

            $cache->save($seo.".cache", $data);
        }

        $paginator = new PaginatorModel([
            'data'  => Comments::getComments($server->id),
            'limit' => 10,
            'page'  => $this->request->getQuery("page", "int", 1)
        ]);

        $this->view->server    = $server;
        $this->view->voteData  = $data['voteData'];
        $this->view->days      = range(1, date('t'));
        $this->view->seo_title = $seo;
        $this->view->likes     = $data['likes'];
        $this->view->comments  = $paginator->getPaginate();
        $this->view->resetIn   = Functions::timeLeft('Y-m-t 23:59:59', '%dd %hh %im %ss');
        return true;
    }

    public function discordAction() {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        if (!$this->request->isPost() ||! $this->request->isAjax()) {
            return false;
        }

        $server_id = $this->request->getPost("server_id", "int");

        $discord = new Discord($server_id);
        $discord->fetch();

        $data = $discord->getRawData();

        if (isset($data->code)) {
            $this->flash->error("Error loading Discord: ".$data->message);
        } else {
            $this->view->discord = $discord;
        }
        return true;
    }

    public function reportAction() {
        if (!$this->request->isPost() /*|| !$this->security->checkToken()*/) {
            return $this->response->redirect("");
        }

        $user_id  = $this->getUser() ? $this->getUser()->id : null;
        $username = $this->getUser() ? $this->getUser()->username : null;
        $serverId = $this->request->getPost("serverId", "int");
        $comment  = nl2br($this->request->getPost("comment", 'string'));

        $server = Servers::getServer($serverId);

        if (!$server) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        $this->view->seo_title = Servers::genSeoTitle($server);
        $this->view->server = $server;

        $lastReport = Reports::getRecentReport($user_id, $serverId);

        if ($lastReport) {
            $this->view->saved = false;
            $this->view->error = "You have already submitted a report on this server within the last 5 minutes. Take a chill pill.";
            return true;
        }

        $report = new Reports;
        $report->setUserId($user_id);
        $report->setUsername($username);
        $report->setServerId($server->id);
        $report->setReason($comment);
        $report->setDateSubmitted(time());

        if ($report->save()) {
            $this->view->saved = true;
        } else {
            $this->view->saved = false;
            $this->view->error = $report->getMessages()[0];
        }
    }

    public function likeAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

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

        $user_id = $this->getUser() ? $this->getUser()->id : -1;

        if ($user_id == -1) {
            $this->println([
                'success' => false,
                'message' => 'You must be logged in to like a server!'
            ]);
            return false;
        }

        $like = Likes::getLike($server->id, $user_id);

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
        $this->cookies->set("access_token", '', time() - 1000, base_url);
        $this->session->destroy();
        return $this->response->redirect("");
    }

}