<?php
use \Phalcon\Mvc\Controller;
use \Phalcon\Mvc\Dispatcher;

class BaseController extends Controller {

    /**
     * @var Phalcon\Config
     */
    protected $config;

    private $page_meta = [
        "pages" => [
            "docs" => [
                'title' => 'Documentation',
                'description' => 'Documentation on how to get set up using our toplist to it\'s fullest, including code 
                examples and detailed guides.'
            ],
            "premium" => [
                'description' => 'Need a boost? Purchase premiumn to give you acccess to bonus votes, animated banners, and more!'
            ],
            "faq" => [
                'title' => 'FAQ',
                'description' => 'Get answers to common questions regarding our toplist!'
            ]
        ],
        'servers' => [
            'add' => ['title' => 'Add Server'],
            'edit' => ['title' => 'Edit Server']
        ]
    ];

    public function getDiscordData() {
        $data = [];
        $data['server'] = (new NexusBot())
            ->setIsBot(true)
            ->setEndpoint("guilds/".server_id."")
            ->submit();
        $data['members'] = (new NexusBot())
            ->setIsBot(true)
            ->setEndpoint("guilds/".server_id."/members")
            ->setData(['limit' => 300])
            ->submit();
        $data['invite'] = (new NexusBot())
            ->setIsBot(true)
            ->setEndpoint("guilds/".server_id."/invites")
            ->submit();
        $data['bans'] = (new NexusBot())
            ->setIsBot(true)
            ->setEndpoint("guilds/".server_id."/bans")
            ->submit();
        $data['roles'] = (new NexusBot())
            ->setIsBot(true)
            ->setEndpoint("guilds/".server_id."/roles")
            ->submit();
        return $data;
    }

    /**
     * Redirects controller to errors controller with given error #
     * @param int $type
     */
    public function showError($type) {
        $this->dispatcher->forward([
            'controller' => 'errors',
            'action' => "show{$type}"
        ]);
    }

    public function printData($data) {
        echo "<pre>".json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES)."</pre>";
    }

    public function tidyText($text) {
        $tidyConfig = [
            'clean'          => true,
            'output-xhtml'   => true,
            'show-body-only' => true,
            'wrap'           => 0,
        ];

        $tidy = new Tidy;
        $tidy->parseString($text, $tidyConfig, 'utf8');
        $tidy->cleanRepair();

        return $tidy;
    }

    /**
     * Generates an SEO friendly title
     * @param $server
     * @return string
     */
    public function genSeoTitle($server) {
        $reps = [
            ' - ' => '-',
            ' ' => '-'
        ];

        $seo_title = $server->id.'-'.str_replace(
                array_keys($reps), array_values($reps),
                strtolower($this->filter->sanitize($server->title, "string")));

        return $seo_title;

    }

    public function logout() {
        if (!$this->cookies->has("access_token")) {
            return false;
        }

        $userInfo = (new NexusBot())
            ->setEndpoint("oauth2/token/revoke")
            ->setType('post')
            ->setContentType("x-www-form-urlencoded")
            ->setData([
                "token"         => $this->cookies->get("access_token"),
                'client_id'     => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
            ])
            ->submit();

        if (isset($userInfo['code']) && $userInfo['code'] == 0) {
            $this->cookies->delete("access_token");
            $this->session->destroy();
            return false;
        }

        $this->cookies->delete("access_token");
        $this->session->destroy();
        return true;
    }

    public function getUser() {
        return $this->session->get("user");
    }

    public function getUserAvatar() {
        $user    = $this->getUser();
        $user_id = $user->id;
        $hash    = $user->avatar;
        $isGif   = substr($hash, 0, 2) == "a_";

        $base_url   = "https://cdn.discordapp.com/avatars/";
        return $base_url.$user_id.'/'.$hash.'.'.($isGif ? 'gif' : 'png').'';
    }

    function getRealIp(){
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } else if(filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    /**
     * @param $msg array
     */
    public function println($msg) {
        echo json_encode($msg, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $success bool
     * @param $message string
     */
    public function printStatus($success, $message) {
        $this->println([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * @param $success
     * @param $message
     * @return array
     */
    public function getStatus($success, $message) {
        return [
            'success' => $success,
            'message' => $message
        ];
    }

    /**
     * @param $msg array
     */
    public function debug($msg) {
        echo "<pre>".htmlspecialchars(json_encode($msg, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))."</pre>";
    }

    /**
     * get access token from header
     */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Gets the authorization header.
     * @return string|null
     */
    public function getAuthorizationHeader() {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    public function beforeExecuteRoute(\Phalcon\Dispatcher $dispatcher) {
        $controller = $this->router->getControllerName();
        $action = $this->router->getActionName() ?: $controller;

        $this->logVisitor();

        $this->config = $this->getConfig();

        if (isset($this->page_meta[$controller], $this->page_meta[$controller][$action]) ) {
            $meta = $this->page_meta[$controller][$action];

            if (isset($meta['title'])) {
                $this->tag->setTitle($meta['title']);
            } else {
                $this->tag->setTitle(ucwords($action));
            }
            if (isset($meta['description'])) {
                $this->view->description = $meta['description'];
            }
        } else {
            $this->tag->setTitle(ucwords($action));
        }
    }

    private function logVisitor() {
        if ($this->request->isAjax()) {
            return;
        }

        $ref = new Referrals([
            'ip_address' => $this->getRealIp(),
            'location'   => $this->router->getRewriteUri(),
            'referrer'   => $this->request->getHTTPReferer(),
            'date_added' => time(),
        ]);

        try {
            $ref->save();
        } catch (Exception $e) {

        }
    }

    /**
     * @return mixed|Phalcon\Config
     */
    public function getConfig() {
        return $this->getDI()->get("config");
    }

    public function getThemes() {
        $path  = $this->config->path("core.base_path");
        $dir   = $path.'/public/css/themes/';
        $files = array_values(array_diff(scandir($dir), array('..', '.')));

        $list = [];

        foreach($files as $file) {
            $list[] = [
                'file'     => $file,
                'created'  => date("m/d/Y g:i A", filectime($dir.$file)),
                'modified' => date("m/d/Y g:i A", filemtime($dir.$file))
            ];
        }

        return $list;
    }

    public function themeExists($name) {
        $path = $this->config->path("core.base_path");
        return file_exists($path.'/public/css/themes/'.$name);
    }

    public function getThemeFile($name) {
        $path = $this->config->path("core.base_path");
        return file_get_contents($path.'/public/css/themes/'.$name);
    }
}