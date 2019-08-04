<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface;

class Images extends Model {

    private $user_id;
    private $server_id;
    private $url;

    /**
     * @param $userId
     * @param $serverId
     * @return ResultsetInterface|Images
     */
    public static function getImages($userId, $serverId) {
        return self::query()
            ->conditions("user_id = :uid: AND server_id = :sid:")
            ->bind([
                'uid' => $userId,
                'serverId' => $serverId
            ])->execute();
    }

    /**
     * @param $id
     * @return bool|ModelInterface|Images
     */
    public static function getImage($id) {
        return self::query()
            ->conditions("id = :id:")
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
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
     * @return Images
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
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
     * @return Images
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return Images
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }


}