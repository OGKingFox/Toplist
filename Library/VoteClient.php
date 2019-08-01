<?php

class VoteClient {

    private static $instance;

    /**
     * @return VoteClient
     * Constructs an instance of this class if one does not exist already.
     */
    public static function getClient() {
        if (self::$instance == null) {
            self::$instance = new VoteClient();
        }
        return self::$instance;
    }

    private $url = "http://localhost/toplist/vote";
    private $access_token = "eqYFa4OxeMCbARZ";
    private $data;

    /**
     * @param mixed $data
     * @return VoteClient
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * @param bool $asArray
     * @return mixed
     */
    public function submit($asArray = false) {
        $ch  = curl_init($this->url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            "Authorization: Bearer " . $this->access_token
        ]);

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }
}