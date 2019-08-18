<?php

class NexusBot {

    const valid_types = ['get', 'post', 'put', 'delete', 'patch'];
    const post_types  = ['post', 'put', 'delete', 'patch'];

    const base_url = "https://discordapp.com/api/";

    private $endpoint;
    private $data = [];
    private $type = "get";
    private $is_bot = false;
    private $access_token = null;
    private $content_type = "application/json";

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param mixed $endpoint
     * @return NexusBot
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return NexusBot
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return NexusBot
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        return $this->is_bot;
    }

    /**
     * @param bool $is_bot
     * @return NexusBot
     */
    public function setIsBot($is_bot)
    {
        $this->is_bot = $is_bot;
        return $this;
    }

    /**
     * @return null
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param null $access_token
     * @return NexusBot
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType() {
        return $this->content_type;
    }

    /**
     * @param string $content_type
     * @return NexusBot
     */
    public function setContentType($content_type) {
        $this->content_type = $content_type;
        return $this;
    }

    public function submit() {
        global $config;

        $ch  = curl_init();
        $url = self::base_url.$this->getEndpoint();

        $type = strtolower($this->getType());

        if (!in_array($type, self::valid_types)) {
            return ['error' => 'Invalid type. Valid types: '.implode(",", self::valid_types)];
        }

        if (in_array($type, self::post_types)) {
            if ($type != "post")
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->getType()));
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($this->getData()) {
                $data = $this->isBot()
                    ? json_encode($this->getData(), JSON_UNESCAPED_SLASHES)
                    : http_build_query($this->getData());

                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        } else if ($type == "get") {
            if ($this->getData()) {
                $url = $url . '?' . http_build_query($this->getData());
            }
        }

        if ($this->isBot()) {
            $headers = [
                "Authorization: Bot ".$config->path("discord.bot_key"),
                "Content-Type: ".$this->getContentType()
            ];
        } else {
            $headers = [
                'Accept: '.$this->getContentType(),
                "Authorization: Bearer " . $this->getAccessToken()
            ];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }


}