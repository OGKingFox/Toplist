<?php

class UsersController extends \Phalcon\Mvc\Controller {

    /**
     * Gets member info from the forum by name
     * @param $name
     * @return Member|null
     */
    public function find($name) {
        return $this->getMember($name);
    }

}