<?php
use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Uniqueness;

class ServersInfo extends Model {

    private $server_id;
    private $website;
    private $callback;
    private $discord_id;
    private $banner_url;
    private $meta_info;
    private $meta_tags;
    private $info;

    public static function getServerInfo($server_id) {
        return self::query()
            ->conditions("server_id = :sid:")
            ->bind([
                'sid' => $server_id,
            ])->execute()->getFirst();
    }

    /**
     * @return mixed
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    /**
     * @param mixed $server_id
     * @return ServersInfo
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     * @return ServersInfo
     */
    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param mixed $callback
     * @return ServersInfo
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscordId()
    {
        return $this->discord_id;
    }

    /**
     * @param mixed $discord_id
     * @return ServersInfo
     */
    public function setDiscordId($discord_id)
    {
        $this->discord_id = $discord_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBannerUrl()
    {
        return $this->banner_url;
    }

    /**
     * @param mixed $banner_url
     * @return ServersInfo
     */
    public function setBannerUrl($banner_url)
    {
        $this->banner_url = $banner_url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMetaInfo()
    {
        return $this->meta_info;
    }

    /**
     * @param mixed $meta_info
     * @return ServersInfo
     */
    public function setMetaInfo($meta_info)
    {
        $this->meta_info = $meta_info;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMetaTags()
    {
        return $this->meta_tags;
    }

    /**
     * @param mixed $meta_tags
     * @return ServersInfo
     */
    public function setMetaTags($meta_tags)
    {
        $this->meta_tags = $meta_tags;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     * @return ServersInfo
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    public function validation() {
        $validator = new Validation();

        $validator->add("discord_id", new Callback([
            "callback" => function() {
                return preg_match('/^[0-9]+$/i', $this->discord_id) !== false;
            },
            "message" => "Invalid discord id format."
        ]));

        $validator->add("website", new Callback([
            "callback" => function() {
                return !$this->website || ($this->website && filter_var($this->website, FILTER_VALIDATE_URL) == true);
            },
            "message" => "Invalid website address."
        ]));

        $validator->add("callback", new Callback([
            "callback" => function() {
                return !$this->callback || ($this->callback && filter_var($this->callback, FILTER_VALIDATE_URL) == true);
            },
            "message" => "Invalid callback url."
        ]));

        $validator->add("meta_info", new Callback([
            "callback" => function() {
                return strlen($this->meta_info) <= 160;
            },
            "message" => "Meta description can not be longer than 160 characters."
        ]));

        $validator->add("meta_tags", new Callback([
            "callback" => function() {
                return count(json_decode($this->meta_tags, true)) < 15;
            },
            "message" => "You can not have more than 15 meta tags."
        ]));

        return $this->validate($validator) == true;
    }




}