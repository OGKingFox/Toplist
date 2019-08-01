<?php

use Phalcon\Text;

class VoteController extends BaseController {

    public function indexAction($server, $incentive) {
        $server    = Servers::getServer($this->filter->sanitize($server, "int"));
        $incentive = $this->filter->sanitize($incentive, 'string');

        if (!$server) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        $this->view->server    = $server;
        $this->view->incentive = $incentive;
        return true;
    }

    public function verifyAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);

        if (!$this->request->isPost()) {
            $this->println([
                'success' => false,
                'message' => 'This page is available via post only.'
            ]);
            return false;
        }

        $server_id = $this->request->getPost("server", 'int');
        $incentive = $this->request->getPost("incentive", "string");
        $token     = $this->request->getPost("token");
        $server    = Servers::getServer($server_id);

        if (!$server) {
            $this->println([
                'success' => false,
                'message' => 'The server you\'re trying to vote for does not exist.'
            ]);
            return true;
        }

        $recent = Votes::query()
            ->conditions("server_id = :sid: AND (ip_address = :ip: OR incentive = :inc:) AND :time: - voted_on < 43000")
            ->bind([
                'time' => time(),
                'sid'  => $server->id,
                'ip'   => $this->getRealIp(),
                'inc'  => $incentive
            ])
            ->execute()->getFirst();

        if ($recent) {
            $this->println([
                'success' => false,
                'message' => 'You have already voted within the last 12 hours!'
            ]);
            return true;
        }

        $recaptcha = $this->getResponse($token);

        if (!$recaptcha['success']) {
            $this->println([
                'success' => false,
                'message' => 'reCaptcha has failed verification.'
            ]);
            return true;
        }

        $vote = new Votes;
        $vote->setIpAddress($this->getRealIp());
        $vote->setVotedOn(time());
        $vote->setServerId($server->id);
        $vote->setIncentive($incentive);

        if (!$vote->save()) {
            $this->println([
                'success' => false,
                'message' => 'Vote failed to save: '.$vote->getMessages()[0]
            ]);
            return true;
        }

        $callback = $this->sendIncentive($server->callback, $incentive);

        if (is_array($callback)) {
            $response = $callback['response'];
            if (isset($response['success']) && isset($response['message'])) {
                $this->println([
                    'success' => $response['success'],
                    'message' => $response['message']
                ]);
                return true;
            }
        }

        $this->println([
            'success' => true,
            'message' => 'Thank you, your vote has been recorded!'
        ]);
        return true;
    }

    private function getResponse($token) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array('secret' => CAPTCHA_PRIVATE, 'response' => $token);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );

        $context      = stream_context_create($options);
        $response     = file_get_contents($url, false, $context);
        $responseKeys = json_decode($response,true);
        return $responseKeys;
    }

    public function sendIncentive($url, $incentive) {
        $fields = ['callback' => urlencode($incentive)];
        $fields_string = '';

        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }

        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = curl_exec($ch);
        curl_close($ch);

        return [
            'http_code' => $httpcode,
            'response' => json_decode($result, true)
        ];
    }
}