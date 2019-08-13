<?php

use Phalcon\Text;

class VoteController extends BaseController {

    public function indexAction($server = null, $incentive = null) {
        if ($server == null || $incentive == null) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        $server    = Servers::getServer($this->filter->sanitize($server, "int"));
        $incentive = $this->filter->sanitize($incentive, 'string');

        if (!$server) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return true;
        }

        $this->tag->setTitle("Vote for ".$server->title);

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

        $server    = Servers::getServerById($server_id);

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
                'sid'  => $server->servers->id,
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

        $recaptcha = $this->verifyReCaptcha($token);

        if (!$recaptcha['success']) {
            $this->println($recaptcha);
            return true;
        }

        $vote = new Votes;
        $vote->setIpAddress($this->getRealIp());
        $vote->setVotedOn(time());
        $vote->setServerId($server->servers->id);
        $vote->setIncentive($incentive);

        if (!$vote->save()) {
            $this->println([
                'success' => false,
                'message' => 'Vote failed to save: '.$vote->getMessages()[0]
            ]);
            return true;
        }

        $server->servers->votes = $server->servers->votes + 1;
        $server->servers->save();

        $callback = $this->sendIncentive($server->info->callback, $incentive);

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

    private function verifyReCaptcha($recaptchaCode){
        $curl = curl_init("https://www.google.com/recaptcha/api/siteverify");
        $data = ["secret" => CAPTCHA_PRIVATE, "response" => $recaptchaCode];
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        if ($resp == NULL){
            return curl_errno($curl);
        } else {
            return json_decode($resp, true);
        }
    }

    public function sendIncentive($url, $incentive) {
        $fields = ['callback' => urlencode($incentive)];
        $fields_string = '';

        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }

        rtrim($fields_string, '&');

        $header = array(
            'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-us,en;q=0.5',
            'Accept-Encoding: gzip,deflate',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'Keep-Alive: 115',
            'Connection: keep-alive',
        );

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result   = curl_exec($ch);

        curl_close($ch);

        return [
            'http_code' => $httpcode,
            'response'  => json_decode($result, true)
        ];
    }
}