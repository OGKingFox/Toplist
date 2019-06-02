<?php
use Phalcon\Validation;
use \Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Uniqueness;

class Servers extends \Phalcon\Mvc\Model {

    private $owner_id;
    private $owner_tag;
    private $game;
    private $title;
    private $website;
    private $callback;
    private $discord_id;
    private $summary;
    private $info;

    /**
     * Grabs a server by info, and joins in the game id and title.
     * @param $id
     * @return bool|\Phalcon\Mvc\ModelInterface|Servers
     */
    public static function getServer($id) {
        return self::query()
            ->columns([
                'Server.id',
                'Server.owner_id',
                'Server.owner_tag',
                'Server.game',
                'Server.title',
                'Server.website',
                'Server.callback',
                'Server.discord_id',
                'Server.summary',
                'Server.info',
                'g.id AS game_id',
                'g.title AS game_title'
            ])
            ->conditions('Servers.id = :id:')
            ->leftJoin("Games", 'g.title = Servers.game', 'g')
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }

    /**
     * Like above, but doesn't join the games column so it can be updated or removed.
     * @param $id
     * @return bool|\Phalcon\Mvc\ModelInterface|Servers
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

    public function validation() {
        $validator = new Validation();

        $validator->add("title", new Uniqueness([
            "message" => "A server by that name is already registered.",
        ]));

        $validator->add("owner_id", new Uniqueness([
            "message" => "You already have a server registered.",
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

        $validator->add("summary", new Callback([
            "callback" => function() {
                return strlen($this->summary) >= 10 && strlen($this->summary) <= 200
                    && preg_match('/^[A-Za-z0-9 \-_.]+$/i', $this->summary);
            },
            "message" => "Invalid description. Must be between 20 and 200 characters."
        ]));

        $validator->add("summary", new Callback([
            "callback" => function() {
                return preg_match('/^[A-Za-z0-9 \-_.]+$/i', $this->summary) !== false;
            },
            "message" => "Invalid description. Contains invalid characters."
        ]));

        return $this->validate($validator) == true;
    }

}