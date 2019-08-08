<?php
use Phalcon\Mvc\Model;

class Announcements extends Model {

    private $user_id;
    private $news_body;
    private $date_posted;

    public static function getArticle($id) {
        return self::query()
            ->conditions("id = :id:")
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }

    public static function getAnnouncements() {
        return self::query()
            ->columns([
                'Announcements.id',
                'Announcements.user_id',
                'Announcements.news_body',
                'Announcements.date_posted',
                'user.username',
                'user.discriminator',
                'user.role',
                'user.avatar',
            ])
            ->leftJoin("Users", "user.user_id = Announcements.user_id", "user")
            ->execute();
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
     * @return Announcements
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewsBody()
    {
        return $this->news_body;
    }

    /**
     * @param mixed $news_body
     * @return Announcements
     */
    public function setNewsBody($news_body)
    {
        $this->news_body = $news_body;
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
     * @return Announcements
     */
    public function setDatePosted($date_posted)
    {
        $this->date_posted = $date_posted;
        return $this;
    }


}