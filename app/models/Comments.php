<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Uniqueness;

class Comments extends Model {

    private $user_id;
    private $server_id;
    private $username;
    private $avatar;
    private $comment;
    private $date_posted;

    /**
     * @param $server_id
     * @return ResultsetInterface|Comments
     */
    public static function getComments($server_id) {
        return self::query()
            ->columns([
                'Comments.id',
                'Comments.server_id',
                'Comments.user_id',
                'Comments.username',
                'Comments.comment',
                'Comments.date_posted',
                'u.avatar'
            ])
            ->conditions('server_id = :sid:')
            ->leftJoin('Users', 'u.user_id = Comments.user_id', 'u')
            ->bind([
                'sid' => $server_id
            ])
            ->orderBy('date_posted DESC')
            ->execute();
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
     * @return Comments
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
        return $this;
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
     * @return Comments
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
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
     * @return Comments
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
     * @return Comments
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     * @return Comments
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatePosted()
    {
        return $this->date_posted;
    }

    /**
     * @param mixed $date_posted
     * @return Comments
     */
    public function setDatePosted($date_posted)
    {
        $this->date_posted = $date_posted;
        return $this;
    }

    public function validation() {
        $validator = new Validation();

        $validator->add("comment", new Callback([
            "callback" => function() {
                return preg_match('/^[A-Za-z0-9\-._!?#$%^&*()+=,<>:;\s]+$/', $this->comment) == true;
            },
            "message" => "Your comment contains invalid characters."
        ]));


        return $this->validate($validator) == true;
    }


}