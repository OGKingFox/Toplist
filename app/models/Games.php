<?php
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
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
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