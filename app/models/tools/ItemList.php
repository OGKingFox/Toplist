<?php


class ItemList extends \Phalcon\Mvc\Model {

    private $id;
    private $name;
    private $reqs;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ItemList
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return ItemList
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReqs()
    {
        return $this->reqs;
    }

    /**
     * @param mixed $reqs
     * @return ItemList
     */
    public function setReqs($reqs)
    {
        $this->reqs = $reqs;
        return $this;
    }


}