<?php

class NexusBot {
    private $message;
    private $response;
    private $name;
    private $avatar;
    private $channel;

    public function setChannel($channel) {
        $this->channel = $channel;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getResponse() {
        return $this->response;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setAvatar($avatar) {
        $this->avatar = $avatar;
    }

    public function send() {
        $data = [];

        $data['content']  = $this->message;

        if ($this->name)
            $data['username'] = $this->name;
        if ($this->avatar)
            $data['avatar'] = $this->avatar;
        if ($this->channel)
            $data['channel_id'] = $this->channel;

        $json_data = json_encode($data);

        $ch = curl_init(webhook_url);

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response    = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header      = substr($response, 0, $header_size);
        $body        = substr($response, $header_size);

        curl_close($ch);

        $this->response = ['header' => $header, 'resp' => $response, 'body' => $body];
        return json_encode($this->response);
    }
}