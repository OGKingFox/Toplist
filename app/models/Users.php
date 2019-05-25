<?php
/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 5/25/19
 * Time: 4:55 PM
 */

class Users extends \Phalcon\Mvc\Model {

    private $username;
    private $verified;
    private $locale;
    private $premium_type;
    private $mfa_enabled;
    private $user_id;
    private $flags;
    private $avatar;
    private $discriminator;

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * @param mixed $verified
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getPremiumType()
    {
        return $this->premium_type;
    }

    /**
     * @param mixed $premium_type
     */
    public function setPremiumType($premium_type)
    {
        $this->premium_type = $premium_type;
    }

    /**
     * @return mixed
     */
    public function getMfaEnabled()
    {
        return $this->mfa_enabled;
    }

    /**
     * @param mixed $mfa_enabled
     */
    public function setMfaEnabled($mfa_enabled)
    {
        $this->mfa_enabled = $mfa_enabled;
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

    /**
     * @return mixed
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param mixed $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
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
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
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
     */
    public function setDiscriminator($discriminator)
    {
        $this->discriminator = $discriminator;
    }



}