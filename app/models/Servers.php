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
    private $title;
    private $votes;
    private $game;
    private $date_created;

    public static function getNewestServers() {
        return self::query()
            ->columns([
                'Servers.id',
                'Servers.title',
                'Servers.date_created',
                'info.*',
            ])
            ->leftJoin("ServersInfo", 'info.server_id = Servers.id AND info.website != \'\'', 'info')
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
                'info.*',
                'IF(user.premium_expires > :time:, Servers.votes + (user.premium_level * 100), Servers.votes) AS votes'
            ])
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
            ->leftJoin("ServersInfo", 'info.server_id = Servers.id AND info.website != \'\'', 'info')
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
        $query = self::query()->columns(
            [
                'Servers.id',
                'Servers.owner_id',
                'Servers.owner_tag',
                'Servers.title',
                'IF(user.premium_expires > :time:, Servers.votes + (user.premium_level * 100), Servers.votes) AS votes',
                'info.*',
                'user.*'
            ])
            ->conditions('Servers.game = :gid: AND info.website != \'\'')
            ->bind([
                    'gid' => $gameId,
                    'time' => time()
            ])
            ->leftJoin("ServersInfo", 'info.server_id = Servers.id AND info.website != \'\'', 'info')
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
                'Servers.date_created',
                'IF(user.premium_expires > :time:, Servers.votes + (user.premium_level * 100), Servers.votes) AS votes',
                'user.*',
                'info.*',
                'ss.images'
            ])
            ->conditions('Servers.id = :id:')
            ->leftJoin("ServersInfo", 'info.server_id = Servers.id AND info.website != \'\'', 'info')
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
     * @param $ownerId
     * @return bool|ModelInterface|Servers
     */
    public static function getServerByOwner($serverId, $ownerId) {
        return self::query()
            ->columns([
                'Servers.*',
                'user.*',
                'info.*',
            ])
            ->conditions('Servers.id = :id: AND Servers.owner_id = :owner:')
            ->leftJoin("ServersInfo", 'info.server_id = Servers.id', 'info')
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
            ->bind([
                'id' => $serverId,
                'owner' => $ownerId
            ])->execute()->getFirst();
    }

    /**
     * Like above, but doesn't join the games column so it can be updated or removed.
     * @param $serverId
     * @param $ownerId
     * @return bool|ModelInterface|Servers
     */
    public static function getServerByOwner2($ownerId) {
        return self::query()
            ->columns([
                'Servers.*',
                'user.*',
                'info.*',
            ])
            ->conditions('Servers.owner_id = :owner:')
            ->leftJoin("ServersInfo", 'info.server_id = Servers.id', 'info')
            ->leftJoin("Users", 'user.user_id = Servers.owner_id', 'user')
            ->bind([
                'owner' => $ownerId
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
    public function getTitle()
    {
        return $this->title;
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
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param mixed $date_created
     * @return Servers
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
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

    public function validation() {
        $validator = new Validation();

        $validator->add("title", new Uniqueness([
            "message" => "A server by that name is already registered.",
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

        return $this->validate($validator) == true;
    }

}