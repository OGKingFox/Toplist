<?php

use Phalcon\Mvc\ModelInterface;

class Packages extends \Phalcon\Mvc\Model {

    private $title;
    private $price;
    private $features;
    private $length;
    private $active;

    /**
     * @param $id
     * @return bool|ModelInterface|Packages
     */
    public static function getPackage($id) {
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
     * @return Packages
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return Packages
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param mixed $features
     * @return Packages
     */
    public function setFeatures($features)
    {
        $this->features = $features;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     * @return Packages
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     * @return Packages
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }




}