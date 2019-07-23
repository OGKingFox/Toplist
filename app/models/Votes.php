<?php
/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 6/2/19
 * Time: 11:57 PM
 */

class Votes extends \Phalcon\Mvc\Model {

    private $server_id;
    private $ip_address;
    private $voted_on;

    public static function getVoteTotalForMonth($serverId) {
        $start = strtotime(date("Y-m-1 00:00:00"));
        $end   = strtotime(date("Y-m-t 23:59:59"));

        return self::query()
            ->columns("COUNT(*) AS total")
            ->conditions("server_id = :sid: AND voted_on BETWEEN :start: AND :end:")
            ->bind([
                'sid'   => $serverId,
                'start' => $start,
                'end'   => $end
            ])->execute()->getFirst();
    }

    public static function getVotesForMonth($serverId) {
        $start = strtotime(date("Y-m-1 00:00:00"));
        $end   = strtotime(date("Y-m-t 23:59:59"));

        /** @var $votes Votes */
        $votes = self::query()
            ->conditions("server_id = :sid: AND voted_on BETWEEN :start: AND :end:")
            ->bind([
                'sid'   => $serverId,
                'start' => $start,
                'end'   => $end
            ])->execute();

        $dates = Functions::getDaysOfMonth();

        /** @var $vote Votes */
        foreach ($votes as $vote) {
            $date = date("Y-m-j", $vote->getVotedOn());
            $dates[$date] += 1;
        }

        return $dates;
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
     * @return Votes
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param mixed $ip_address
     * @return Votes
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVotedOn()
    {
        return $this->voted_on;
    }

    /**
     * @param mixed $voted_on
     * @return Votes
     */
    public function setVotedOn($voted_on)
    {
        $this->voted_on = $voted_on;
        return $this;
    }


}