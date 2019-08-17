<?php


class Reports extends \Phalcon\Mvc\Model {

    private $user_id;
    private $username;
    private $server_id;
    private $reason;
    private $date_submitted;

    public static function getReport($id) {
        return self::query()
            ->conditions("id = :id:")
            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }
    public static function getReports() {
        return self::query()
            ->columns([
                'Reports.*',
                'Servers.*',
                'Users.*'
            ])
            ->leftJoin("Servers", "Servers.id = Reports.server_id")
            ->leftJoin("Users", "Users.user_id = Reports.user_id")
            ->orderBy("Reports.date_submitted DESC")
            ->execute();
    }

    public static function getRecentReport($userId, $serverId) {
        return self::query()
            ->conditions('user_id = :uid: AND server_id = :sid: AND :time: - date_submitted < 300')
            ->bind([
                'time' => time(),
                'uid' => $userId,
                'sid' => $serverId
            ])
            ->orderBy("date_submitted DESC")
            ->limit(1)
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
     * @return Reports
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
     * @return Reports
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
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
     * @return Reports
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     * @return Reports
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateSubmitted()
    {
        return $this->date_submitted;
    }

    /**
     * @param mixed $date_submitted
     * @return Reports
     */
    public function setDateSubmitted($date_submitted)
    {
        $this->date_submitted = $date_submitted;
        return $this;
    }




}