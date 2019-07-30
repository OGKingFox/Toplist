<?php
/**
 * Author: King Fox (https://www.foxtrot-studios.co/)
 */
class Discord {

    public $serverId;
    public $data;
    public $channels;
    public $members;

    /**
     * Discord constructor.
     * @param $serverId
     */
    public function __construct($serverId) {
        $this->serverId = $serverId;
    }

    /**
     * Fetches data from the discord server API.
     */
    public function fetch() {
        $url = 'https://discordapp.com/api/servers/'.$this->serverId.'/widget.json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $this->data = json_decode(curl_exec($ch));
        curl_close($ch);
    }

    /**
     * @return mixed
     */
    public function getServerTitle() {
        return $this->data->name;
    }

    /**
     * @return mixed
     */
    public function getRawData() {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getChannels() {
        return $this->data->channels;
    }

    /**
     * @return mixed
     */
    public function getMembers() {
        return $this->data->members;
    }

    /**
     * @return int
     */
    public function getMemberCount() {
        return count($this->data->members);
    }

    /**
     * @param $id
     * @return array
     */
    public function getMembersInChannel($id) {
        if ($id == null) {
            die('Server Id can not be null.');
        }
        $members = array_filter($this->getMembers(), function($member) use ($id) {
            if (!isset($member->channel_id))
                return false;
            if ($member->channel_id != $id)
                return false;
            if (isset($member->bot))
                return false;
            return true;
        });
        return $members;
    }
}
?>