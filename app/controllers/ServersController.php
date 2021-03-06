<?php
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Http\Request\File;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Tag;
use Phalcon\Text;

class ServersController extends BaseController {

    private static $max_size = (1024 * 1024 * 5); // max size in bytes

    public function indexAction($gameId = 1) {
        $this->tag->setTitle("Server List");

        $gameId = $this->filter->sanitize($gameId, is_numeric($gameId) ? 'int' : 'string');
        $game   = Games::getGameByIdOrName($gameId);

        if (!$game) {
            return $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
        }

        $servers = Servers::getServers($game ? $game->getId() : null);

        $serverList = (new PaginatorModel([
            'data'  => $servers,
            'limit' => 30,
            'page'  => $this->dispatcher->getParam("page", "int", 1)
        ]))->getPaginate();

        $this->view->game      = $game;
        $this->view->games     = Games::find();
        $this->view->servers   = $serverList;
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

        $this->setMetaInfo($server);

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
                    return $this->response->redirect('servers/view/'.Servers::genSeoTitle($server)."#comments");
                }
            }
        }

        $comments = (new PaginatorModel([
            'data'  => Comments::getComments($server->id),
            'limit' => 5,
            'page'  => $this->request->getQuery("page", "int", 1)
        ]))->getPaginate();

        $this->view->server    = $server;
        $this->view->likes     = Likes::count(['conditions' => 'server_id = '.$server->id]);
        $this->view->comments  = $comments;
        $this->view->resetIn   = Functions::timeLeft('Y-m-t 23:59:59', '%dd %hh %im %ss');

        $graphData = $this->getGraphData($server, 13);
        $this->view->days = array_column($graphData, 'time');
        $this->view->data = array_column($graphData, 'total');
        return true;
    }

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
                $host = $this->request->getPost("server_ip", "string");
                $port = $this->request->getPost("server_port", "string");

                $server = new Servers($this->request->getPost());
                $server->setOwnerId($this->getUser()->id);
                $server->setOwnerTag($this->getUser()->username.'#'.$this->getUser()->discriminator);
                $server->setDateCreated(time());

                $socket = fsockopen($host, $port, $errno, $errstr, 1);
                $server->setOnline($socket ? 1 : 0);
                fclose($socket);

                if (!$server->save()) {
                    $this->flash->error($server->getMessages());
                } else {
                    $sinfo = new ServersInfo();
                    $sinfo->setServerId($server->getId());
                    $sinfo->setWebsite($this->request->getPost("website", "url"));
                    $sinfo->setDiscordId($this->request->getPost("discord_id", "int"));
                    $sinfo->setCallback($this->request->getPost("callback", "url"));
                    $sinfo->setInfo(Functions::getPurifier()->purify($this->request->getPost("info")));
                    $sinfo->save();

                    if ($sinfo->getWebsite()) {
                        $seo  = Servers::genSeoTitle($server);
                        $title = $server->getTitle();

                        (new BotMessage([
                            'channel' => '607320502268330016',
                            'title'   => 'New Server!',
                            'message' => "{$this->getUser()->username} has listed a new server: [{$title}](http://rune-nexus.com/servers/view/{$seo})",
                            'is_rich' => false
                        ]))->send();
                    }
                    return $this->response->redirect("servers/view/".$server->getSeoTitle());
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

        $user_id = $this->getUser()->id;
        $server = Servers::getServerByOwner($id, $user_id);

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

        /** @var Servers $mainInfo */
        $mainInfo = $server->servers;
        /** @var Users $user */
        $user     = $server->user;

        /** @var ServersInfo $details */
        $details  = ServersInfo::getServerInfo($mainInfo->id);

        if ($this->request->isPost() /*&& $this->security->checkToken()*/) {
            $owner = $this->getUser();

            $mainInfo->setTitle($this->request->getPost("title", 'string', $mainInfo->getTitle()));
            $mainInfo->setGame($this->request->getPost("game", "int", $mainInfo->getGame()));
            $mainInfo->setServerIp($this->request->getPost("server_ip", "string", $mainInfo->getServerIp()));
            $mainInfo->setServerPort($this->request->getPost("server_port", "int", $mainInfo->getServerPort()));

            if (!$mainInfo->update()) {
                $this->flash->error($mainInfo->getMessages());
            } else {
                $infoBox = $this->request->getPost("info", null, $details->info);
                $infoBox = Functions::getPurifier()->purify($infoBox);

                $details = $details ? $details : new ServersInfo();
                $details->setServerId($mainInfo->id);
                $details->setWebsite($this->request->getPost("website", "url", $details->getWebsite()));
                $details->setCallback($this->request->getPost("callback", "url", $details->getCallback()));
                $details->setDiscordId($this->request->getPost("discord_id", "url", $details->getDiscordId()));
                $details->setMetaInfo($this->request->getPost("meta_info", "url", $details->getMetaInfo()));
                $details->setInfo($infoBox);

                if ($this->request->hasPost("meta_tags")) {
                    $meta_tags = explode(",", $this->request->getPost("meta_tags", 'string'));
                    $details->setMetaTags(json_encode($meta_tags));
                }

                if (!$details->save()) {
                    $this->flash->error($details->getMessages());
                } else {
                    $this->session->set("notice", [
                        'type' => 'success',
                        'message' => 'Your server has been updated.'
                    ]);
                    return $this->response->redirect("servers/edit/".$mainInfo->id);
                }
            }
        }

        $this->view->games  = Games::find();
        $this->view->server = $mainInfo;
        $this->view->info   = $details;
        $this->view->serverImages = Screenshots::getScreenshots($mainInfo->id, $user_id);
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

        $server->info->delete();
        $server->servers->delete();

        $this->printStatus(true, 'This server has been removed.');
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

    public function removeimageAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->printStatus(false, "Invalid request.");
            return false;
        }

        $owner_id  = $this->getUser()->id;
        $server_id = $this->request->getPost("server_id", "int");
        $image_url = $this->request->getPost("image", "url");

        $images = Screenshots::getScreenshots($server_id, $owner_id);

        if (!$images) {
            $this->printStatus(true, 'No images to remove!');
            return false;
        }

        $images->removeImage($image_url);

        if (!$images->save()) {
            $this->printStatus(false, 'Failed to update: '.$images->getMessages()[0]);
            return false;
        }

        $this->printStatus(true, 'Image removed successfully.');
        return true;
    }

    public function imagesAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        if (!$this->request->isPost() || !$this->request->isAjax() || !$this->request->hasFiles()) {
            $this->printStatus(false, "Invalid request.");
            return false;
        }

        $userId = $this->getUser()->id;
        $server_id = $this->request->getPost("server_id", "int");
        $server = Servers::getServerByOwner($server_id, $userId);

        if (!$server) {
            $this->printStatus(false, 'Failed to load server info. '.$server_id);
            return false;
        }

        $screenshots = Screenshots::getScreenshots($server->servers->id, $userId);

        if (!$screenshots) {
            $screenshots = new Screenshots;
            $screenshots->setServerId($server->servers->id);
            $screenshots->setOwnerId($userId);
            $screenshots->setImages('[]');
        }

        $images = $screenshots->getImages();

        if ($images && count($images) == 10) {
            $this->printStatus(false, 'You can not have any more images.');
            return false;
        }

        $files = $this->request->getUploadedFiles();

        $valid_types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        $maxDims = [1600, 900];
        $maxSize = self::$max_size;

        $count = 0;
        $links = [];

        foreach ($files as $file) {
            if ($count >= 5) {
                break;
            }

            $type   = $file->getRealType();
            $size   = $file->getSize();
            $ext    = $file->getExtension();
            $dims   = getimagesize($file->getTempName());
            $width  = $dims[0];
            $height = $dims[1];

            if (!in_array($type, array_values($valid_types)) || !in_array($ext, array_keys($valid_types))) {
                continue;
            }

            if ($size > $maxSize) {
                continue;
            }

            if ($width > $maxDims[0] || $height > $maxDims[1]) {
                continue;
            }

            if (count($images) == 10) {
                break;
            }

            $upload = $this->uploadImage($file);

            if (isset($upload['error'])) {
                $this->printStatus(true, $upload['error']);
                break;
            }

            $images[] = $upload['link'];
            $links[]  = $upload['link'];
            $count++;
        }

        $screenshots->setImages(json_encode($images, JSON_UNESCAPED_SLASHES));

        if (!$screenshots->save()) {
            $this->printStatus(true, $screenshots->getMessages()[0]);
            return false;
        }

        $this->println([
            'success'   => true,
            'server_id' => $server->servers->id,
            'message'   => $links
        ]);
        return true;
    }

    public function bannerAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        if (!$this->request->isPost() || !$this->request->isAjax() || !$this->request->hasFiles()) {
            $this->printStatus(false, "Invalid request.");
            return false;
        }

        $file   = $this->request->getUploadedFiles()[0];
        $type   = $file->getRealType();
        $size   = $file->getSize();
        $ext    = $file->getExtension();

        $dims   = getimagesize($file->getTempName());
        $width  = $dims[0];
        $height = $dims[1];

        $valid_types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        $maxDims = [468, 60];
        $maxSize = self::$max_size;

        if (!in_array($type, array_values($valid_types)) || !in_array($ext, array_keys($valid_types))) {
            $this->printStatus(false, "Invalid type. Allowed: ".implode(',', $valid_types));
            return false;
        }

        if ($size > $maxSize) {
            $this->printStatus(false, "Image can not exceed ".(($maxSize/1024)/1024)."MB.");
            return false;
        }

        if ($width != $maxDims[0] && $height != $maxDims[1]) {
            $this->printStatus(false, "Image must be $maxDims[0]px x $maxDims[1]px.");
            return false;
        }

        $userId = $this->getUser()->id;
        $sid    = $this->request->getPost("serverId", "int");
        $server = Servers::getServerByOwner($sid, $userId);

        if (!$server) {
            $this->printStatus(true, "Upload failed, could not locate this server!");
            return false;
        }

        $details = ServersInfo::getServerInfo($server->servers->id);
        $upload  = $this->uploadImage($file);

        if (isset($upload['error'])) {
            $this->printStatus(true, $upload['error']);
            return false;
        }

        $details->banner_url = $upload['link'];

        if (!$details->save()) {
            $this->printStatus(false, $server->getMessages()[0]);
            return false;
        }

        $this->printStatus(true, $upload['link']);
        return true;
    }

    /**
     * @param $file File
     * @return array
     */
    private function uploadImage($file) {
        $handle = fopen($file->getTempName(), 'r');
        $encode = base64_encode(fread($handle, filesize($file->getTempName())));
        $query  = http_build_query(['image' => $encode]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://api.imgur.com/3/image");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Client-ID '.$this->config->path("imgur.api_key")]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = json_decode(curl_exec($ch), true);
        $errors = curl_error($ch);
        curl_close($ch);

        if ($errors) {
            return ['error' => $errors ];
        } else {
            return ['link' => $output['data']['link']];
        }
    }

    private function setMetaInfo($server) {
        $this->tag->setTitle($server->title);

        if ($server->info->meta_info) {
            $this->view->description = $server->info->meta_info;
        }

        if ($server->info->meta_tags) {
            $tags = implode(",", json_decode($server->info->meta_tags, true));
            $this->view->meta_tags = $this->filter->sanitize($tags, 'string');
        }
    }

    /**
     * @param $server
     * @param int $days
     * @return array|mixed|null
     */
    public function getGraphData($server, $days = 14) {
        $seo   = $server->id.'-'.Tag::friendlyTitle($server->title);
        $cache = new BackFile(new FrontData(), ['cacheDir' => "../app/compiled/servers/statistics/"]);
        $data  = $cache->get($seo.'.cache', 600);

        if (!$data) {
            $timeInSecs = (60 * 60 * 24 * $days);

            $data = Votes::query()
                ->columns([
                    "FROM_UNIXTIME(voted_on, '%m/%d') AS time",
                    'COUNT(*) AS total'
                ])
                ->conditions("server_id = :sid: AND UNIX_TIMESTAMP() - voted_on < $timeInSecs")
                ->groupBy("time")
                ->orderBy("time ASC")
                ->bind(['sid' => $server->id])
                ->execute()->toArray();

            $cache->save($seo.'.cache', $data);
        }
        return $data;
    }
}