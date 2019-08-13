<?php

use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Tag;
use Phalcon\Validation;
use \Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Uniqueness;

class Servers extends \Phalcon\Mvc\Model {

    private $owner_id;
    private $owner_tag;
    private $game;
    private $title;
    private $api_key;
    private $website;
    private $banner_url;
    private $votes;
    private $callback;
    private $discord_id;
    private $meta_info;
    private $meta_tags;
    private $info;
    private $likes;
    private $images;
    private $date_created;

    public static function getNewestServers() {
        return self::query()
            ->conditions("website != ''")
            ->orderBy("date_created DESC")
            ->limit(5)
            ->execute();
    }

    /**
     * @return ResultsetInterface|Servers
     */
    public static function getMostVotedOn() {
        $query = self::query()
            ->conditions("website != ''")
            ->columns([
                'Servers.id',
                'Servers.title',
                'IF(user.premium_expires > :time:, Servers.votes + (user.premium_level * 100), Servers.votes) AS votes'
            ])
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
            ->bind([
                'time' => time()
            ])
            ->orderBy("votes DESC")
            ->limit(5)
            ->execute();
        return $query;
    }

    /**
     * @param $gameId
     * @return ResultsetInterface
     */
    public static function getServers($gameId = null) {
        $query =
            self::query()->columns([
                'Servers.id',
                'Servers.owner_id',
                'Servers.owner_tag',
                'Servers.game',
                'Servers.title',
                'Servers.website',
                'Servers.callback',
                'Servers.discord_id',
                'Servers.banner_url',
                'Servers.meta_info',
                'Servers.meta_tags',
                'IF(user.premium_expires > :time:, Servers.votes + (user.premium_level * 100), Servers.votes) AS votes',
                'Servers.info',
                'g.id AS game_id',
                'g.title AS game_title',
                'user.*'
            ])
                ->conditions('Servers.game = :gid: AND Servers.website != \'\'')
                ->bind([
                    'gid' => $gameId,
                    'time' => time()
                ])
                ->leftJoin("Games", 'g.id = Servers.game', 'g')
                ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
                ->orderBy("votes DESC")
            ->execute();
        return $query;
    }

    /**
     * Grabs a server by info, and joins in the game id and title.
     * @param $id
     * @return bool|ModelInterface|Servers
     */
    public static function getServer($id) {
        return self::query()
            ->columns([
                'Servers.id',
                'Servers.owner_id',
                'Servers.owner_tag',
                'Servers.game',
                'Servers.title',
                'Servers.website',
                'Servers.callback',
                'Servers.discord_id',
                'Servers.images',
                'Servers.meta_info',
                'Servers.meta_tags',
                'Servers.info',
                'Servers.date_created',
                'Servers.likes',
                'IF(user.premium_expires > :time:, Servers.votes + (user.premium_level * 100), Servers.votes) AS votes',
                'g.id AS game_id',
                'g.title AS game_title',
                'user.*',
                'ss.images'
            ])
            ->conditions('Servers.id = :id:')
            ->leftJoin("Games", 'g.id = Servers.game', 'g')
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
            ->leftJoin("Screenshots", 'user.user_id = ss.owner_id AND ss.server_id = Servers.id', 'ss')
            ->bind([
                'id' => $id,
                'time' => time()
            ])->execute()->getFirst();
    }

    /**
     * Like above, but doesn't join the games column so it can be updated or removed.
     * @param $serverId
     * @param $oid
     * @return bool|ModelInterface|Servers
     */
    public static function getServerByOwner($serverId, $oid) {
        return self::query()
            ->conditions('id = :sid: AND owner_id = :id:')
            ->bind([
                'sid' => $serverId,
                'id' => $oid
            ])->execute()->getFirst();
    }

    /**
     * Like above, but grabs all servers
     * @param $oid
     * @return ResultsetInterface|Servers
     */
    public static function getServersByOwner($oid) {
        return self::query()
            ->conditions('owner_id = :id:')
            ->bind([
                'id' => $oid
            ])->execute();
    }

    /**
     * Like above, but doesn't join the games column so it can be updated or removed.
     * @param $id
     * @return bool|ModelInterface|Servers
     */
    public static function getServerById($id) {
        return self::query()
            ->conditions('id = :id:')
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }

    /**
     * returns the rows id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @param mixed $owner_id
     * @return Servers
     */
    public function setOwnerId($owner_id)
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwnerTag()
    {
        return $this->owner_tag;
    }

    /**
     * @param mixed $owner_tag
     * @return Servers
     */
    public function setOwnerTag($owner_tag)
    {
        $this->owner_tag = $owner_tag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param mixed $game
     * @return Servers
     */
    public function setGame($game)
    {
        $this->game = $game;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSeoTitle() {
        return self::genSeoTitle($this);
    }

    /**
     * Generates an SEO friendly title
     * @param $server
     * @param $isArr
     * @return string
     */
    public static function genSeoTitle($server, $isArr = false) {
        return ($isArr ? $server['id'] : $server->id).'-'.Tag::friendlyTitle(($isArr ? $server['title'] : $server->title));

    }
    /**
     * @param mixed $title
     * @return Servers
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return Servers
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
     * @return Servers
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
     * @return Servers
     */
    public function setDiscordId($discord_id)
    {
        $this->discord_id = $discord_id;
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
     * @return Servers
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param mixed $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return mixed
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param mixed $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
    }

    public static function getLike($serverId, $user_id) {

    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $api_key
     * @return Servers
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param mixed $votes
     * @return Servers
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
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
     * @return Servers
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
     * @return Servers
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
     * @return Servers
     */
    public function setMetaTags($meta_tags)
    {
        $this->meta_tags = $meta_tags;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImages() {
        return json_decode($this->images, true);
    }

    /**
     * @param mixed $images
     * @return Servers
     */
    public function setImages($images) {
        $this->images = $images;
        return $this;
    }



    public function validation() {
        $validator = new Validation();

        $validator->add("title", new Uniqueness([
            "message" => "A server by that name is already registered.",
        ]));

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

        $validator->add("game", new Callback([
            "callback" => function() { return Games::getGameById($this->game) != null; },
            "message" => "Invalid game."
        ]));

        $validator->add("title", new Callback([
            "callback" => function() {
                return strlen($this->title) >= 4 && strlen($this->title) <= 35;
            },
            "message" => "Invalid title. Must be between 4 and 35 characters"
        ]));

        $validator->add("title", new Callback([
            "callback" => function() {
                return preg_match('/^[A-Za-z0-9 \-]+$/i', $this->title) !== false;
            },
            "message" => "Invalid title. May only contain letters, numbers, spaces, and dashes."
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