<?php
use Phalcon\Mvc\ModelInterface;

class Users extends \Phalcon\Mvc\Model {

    private $user_id;
    private $discriminator;
    private $username;
    private $role;
    private $premium_expires;
    private $premium_level;
    private $email;
    private $avatar;

    /**
     * @param $id
     * @return bool|ModelInterface|Users
     */
    public static function getUser($id) {
        return self::query()
            ->conditions("user_id = :id:")
            ->bind(['id' => $id])
            ->execute()->getFirst();
    }

    /**
     * @param $username
     * @return bool|ModelInterface|Users
     */
    public static function getUserByUsername($username) {
        return self::query()
            ->conditions("username = :name:")
            ->bind(['name' => $username])
            ->execute()->getFirst();
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
     * @return Users
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * @param mixed $discriminator
     * @return Users
     */
    public function setDiscriminator($discriminator)
    {
        $this->discriminator = $discriminator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPremiumExpires()
    {
        return $this->premium_expires;
    }

    /**
     * @param mixed $premium_expires
     * @return Users
     */
    public function setPremiumExpires($premium_expires)
    {
        $this->premium_expires = $premium_expires;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPremiumLevel()
    {
        return $this->premium_level;
    }

    /**
     * @param mixed $premium_level
     * @return Users
     */
    public function setPremiumLevel($premium_level)
    {
        $this->premium_level = $premium_level;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     * @return Users
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     * @return Users
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }




}