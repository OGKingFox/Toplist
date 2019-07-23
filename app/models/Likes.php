<?php
/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 6/2/19
 * Time: 2:30 AM
 */

class Likes extends \Phalcon\Mvc\Model {

    private $server_id;
    private $user_id;

    /**
     * @param $server_id
     * @param $user_id
     * @return bool|\Phalcon\Mvc\ModelInterface|Likes
     */
    public static function getLike($server_id, $user_id) {
        return self::query()
            ->conditions("server_id = :sid: AND user_id = :uid:")
            ->bind([
                'sid' => $server_id,
                'uid' => $user_id
            ])->execute()->getFirst();
    }

    /**
     * @param $server_id
     * @return bool|\Phalcon\Mvc\ModelInterface|Likes
     */
    public static function getLikes($server_id) {
        return self::query()
            ->columns("COUNT(*) AS amount")
            ->conditions("server_id = :sid:")
            ->bind([
                'sid' => $server_id
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
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


}