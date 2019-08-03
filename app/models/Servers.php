<?php

use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface;
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
    private $votes;
    private $callback;
    private $discord_id;
    private $summary;
    private $info;
    private $likes;
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
                '(SELECT COUNT(*) FROM Votes WHERE Votes.server_id = Servers.id) AS votes'
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
        $query = self::query();

        $start  = strtotime(date("Y-m-1 00:00:00"));
        $end    = strtotime(date("Y-m-t 23:59:59"));

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
                'Servers.summary',
                'Servers.votes',
                'Servers.info',
                'g.id AS game_id',
                'g.title AS game_title',
                'user.*'
            ])
            ->conditions('Servers.game = :gid: AND Servers.website != \'\'')
            ->bind([
                'gid' => $gameId
            ])
            ->leftJoin("Games", 'g.id = Servers.game', 'g')
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
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
                'Servers.summary',
                'Servers.info',
                'Servers.date_created',
                'Servers.likes',
                'g.id AS game_id',
                'g.title AS game_title',
                'user.*',
            ])
            ->conditions('Servers.id = :id:')
            ->leftJoin("Games", 'g.id = Servers.game', 'g')
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
            ->bind([
                'id' => $id
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
        $reps = [
            ' - ' => '-',
            ' ' => '-'
        ];

        $title = preg_replace('/[^ \w]+/', '', ($isArr ? $server['title'] : $server->title));
        $title = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $title);
        $title = str_replace(
            array_keys($reps), array_values($reps),
            strtolower($title));

        return ($isArr ? $server['id'] : $server->id).'-'.$title;

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
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param mixed $summary
     * @return Servers
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
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

        /*$validator->add("summary", new Callback([
            "callback" => function() {
                return strlen($this->summary) >= 5 && strlen($this->summary) <= 75;
            },
            "message" => "Invalid summary. Must be between 5 and 75 characters."
        ]));

        $validator->add("summary", new Callback([
            "callback" => function() {
                return preg_match('/^[A-Za-z0-9 \-_.]+$/i', $this->summary) !== false;
            },
            "message" => "Invalid description. Contains invalid characters."
        ]));*/

        return $this->validate($validator) == true;
    }

}