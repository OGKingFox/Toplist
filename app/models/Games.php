<?php

use Phalcon\Tag;

/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 6/2/19
 * Time: 2:30 AM
 */

class Games extends \Phalcon\Mvc\Model {

    private $title;
    private $enabled;

    /**
     * @param $title
     * @return bool|\Phalcon\Mvc\ModelInterface|Games
     */
    public static function getGame($title) {
        return self::query()
            ->conditions("title = :title:")
            ->bind([
                'title' => $title
            ])->execute()->getFirst();
    }

    /**
 * @param $id
 * @return bool|\Phalcon\Mvc\ModelInterface|Games
 */
    public static function getGameById($id) {
        return self::query()
            ->conditions("id = :id:")
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }

    /**
     * @param $id
     * @return bool|\Phalcon\Mvc\ModelInterface|Games
     */
    public static function getGameByIdOrName($id) {
        return self::query()
            ->conditions("id = :id: OR title = :title:")
            ->bind([
                'id' => $id,
                'title' => str_replace("-", " ", $id)
            ])->execute()->getFirst();
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getSeoTitle($includeId = false) {
        return ($includeId ? $this->getId().'-' : '').Tag::friendlyTitle($this->getTitle());
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }


}