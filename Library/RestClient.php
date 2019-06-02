<?php
/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 5/17/19
 * Time: 12:41 PM
 */

class RestClient
{

    private static $instance;

    /**
     * @return RestClient
     * Constructs an instance of this class if one does not exist already.
     */
    public static function getClient() {
        if (self::$instance == null) {
            self::$instance = new RestClient();
        }
        return self::$instance;
    }

    private $url = "https://discordapp.com/api/";
    private $endpoint;
    private $type = "get";
    private $data;
    private $timeout = 30;
    private $use_key = true;
    private $verify_peer = false;
    private $access_token = "";
    private $content_type = "application/json";
    /**
     * @return string
     */
    public function getFullUrl() {
        return $this->getUrl().$this->getEndpoint();
    }

    public function setAccessToken($token) {
        $this->access_token = $token;
        return $this;
    }

    public function getAccessToken() {
        return $this->access_token;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return RestClient
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param mixed $endpoint
     * @return RestClient
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
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
     * @return RestClient
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return RestClient
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return RestClient
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseKey()
    {
        return $this->use_key;
    }

    /**
     * @param bool $use_key
     * @return RestClient
     */
    public function setUseKey($use_key)
    {
        $this->use_key = $use_key;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVerifyPeer()
    {
        return $this->verify_peer;
    }

    /**
     * @param bool $verify_peer
     * @return RestClient
     */
    public function setVerifyPeer($verify_peer)
    {
        $this->verify_peer = $verify_peer;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param string $content_type
     * @return RestClient
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
        return $this;
    }

    /**
     * @param bool $asArray
     * @return mixed
     */
    public function submit($asArray = false) {
        $ch  = curl_init();
        $url = $this->getFullUrl();

        if ($this->getData() != null) {
            if ($this->getType() == 'post') {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->getData()));
            } else if ($this->getType() == 'get') {
                $url =  $url.'?'.http_build_query($this->getData());
            } else if ($this->getType() == 'delete') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->getData()));
            } else {
                return ['error' => 'Type must be either get or post.'];
            }
        }

        if ($this->isUseKey()) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: '.$this->getContentType(),
                "Authorization: Bearer " . $this->getAccessToken()
            ]);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: '.$this->getContentType()
            ]);
        }

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());

        if (!$this->isVerifyPeer()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, $asArray);
    }


}