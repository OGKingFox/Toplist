<?php
use Phalcon\Mvc\Model;

class Articles extends Model {

    private $user_id;
    private $title;
    private $news_body;
    private $date_posted;

    public static function getArticle($id) {
        return self::query()
            ->conditions("id = :id:")
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }

    public static function getArticles() {
        return self::query()
            ->columns([
                'Articles.id',
                'Articles.user_id',
                'Articles.news_body',
                'Articles.title',
                'Articles.date_posted',
                'user.username',
                'user.discriminator',
                'user.role',
                'user.avatar',
            ])
            ->leftJoin("Users", "user.user_id = Articles.user_id", "user")
            ->orderBy("Articles.date_posted DESC")
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
     * @return Articles
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
     * @return Articles
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
     * @return Articles
     */
    public function setDatePosted($date_posted)
    {
        $this->date_posted = $date_posted;
        return $this;
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
     * @return Articles
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }



}