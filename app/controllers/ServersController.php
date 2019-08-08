<?php

use Phalcon\Http\Request\File;
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

        if ($this->request->isPost() && $this->security->checkToken()) {
            $owner  = $this->getUser();

            $server->assign($this->request->getPost());
            $server->setOwnerTag($owner->username.'#'.$owner->discriminator);
            $server->setInfo(Functions::getPurifier()->purify($server->getInfo()));

            $update = true;

            if ($this->request->hasPost("meta_tags")) {
                $meta_tags = explode(",", $this->request->getPost("meta_tags", 'string'));
                $server->setMetaTags(json_encode($meta_tags));
            }

            if ($update) {
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

    public function removeimageAction() {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->printStatus(false, "Invalid request.");
            return false;
        }

        $owner_id  = $this->getUser()->id;
        $server_id = $this->request->getPost("server_id", "int");
        $image_url = $this->request->getPost("image", "url");
        $server    = Servers::getServerByOwner($server_id, $owner_id);

        if (!$server) {
            $this->printStatus(true, 'Failed to load server info. '.$server_id);
            return false;
        }

        $images = $server->getImages();
        $new_images = [];

        for($i = 0; $i < count($images); $i++) {
            if ($images[$i] != $image_url) {
                $new_images[] = $images[$i];
            }
        }

        $server->setImages(json_encode($new_images));

        if (!$server->update()) {
            $this->printStatus(false, 'Failed to update: '.$server->getMessages()[0]);
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

        $files = $this->request->getUploadedFiles();

        $valid_types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        $maxDims = [1600, 900];
        $maxSize = 3145728;

        $userId = $this->getUser()->id;
        $server_id = $this->request->getPost("server_id", "int");
        $server = Servers::getServerByOwner($server_id, $userId);

        if (!$server) {
            $this->printStatus(false, 'Failed to load server info. '.$server_id);
            return false;
        }

        $images = $server->getImages();
        $new_images = [];
        if ($images && count($images) == 10) {
            $this->printStatus(false, 'You can not have any more images.');
            return false;
        }

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

            if (isset($upload['link'])) {
                $images[] = $upload['link'];
                $new_images[] = $upload['link'];
            }

            $count++;
        }

        $server->setImages(json_encode($images, JSON_UNESCAPED_SLASHES));
        $server->update();

        $this->println([
            'success'   => true,
            'server_id' => $server->getId(),
            'message'   => $new_images
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
        $name   = $file->getName();
        $type   = $file->getRealType();
        $size   = $file->getSize();
        $ext    = $file->getExtension();

        $dims   = getimagesize($file->getTempName());
        $width  = $dims[0];
        $height = $dims[1];

        $valid_types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        $maxDims = [468, 60];
        $maxSize = 3145728;

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

        $upload = $this->uploadImage($file);

        if (isset($upload['error'])) {
            $this->printStatus(true, $upload['error']);
            return false;
        }

        $server->setBannerUrl($upload['link']);

        if (!$server->update()) {
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Client-ID '.IMGUR_KEY]);
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
}