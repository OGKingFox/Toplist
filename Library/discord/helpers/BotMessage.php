<?php


class BotMessage extends NexusBot {

    private $channel;
    private $title;
    private $message;
    private $is_rich;

    public function __construct($data = null) {
        if ($data) {
            $this->channel = $data['channel'];
            $this->title   = isset($data['title']) ? $data['title'] : null;
            $this->message = $data['message'];
            $this->is_rich = isset($data['is_rich']) && $data['is_rich'];
        }
    }

    public function send() {
        $data = ['tts' => 'false'];

        if (!$this->is_rich) {
            $data['content'] = $this->message;
        } else {
            $data['embed'] = [
                'title' => $this->title,
                'description' => $this->message
            ];
        }

        $response = $this
            ->setEndpoint("channels/{$this->channel}/messages")
            ->setType("post")
            ->setIsBot(true)
            ->setData([
                "tts" => false,
                'embed' => [
                    'title' => $this->title,
                    'description' => $this->message
                ]
            ])->submit();
        return $response;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     * @return BotMessage
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
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
     * @return BotMessage
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return BotMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsRich()
    {
        return $this->is_rich;
    }

    /**
     * @param mixed $is_rich
     * @return BotMessage
     */
    public function setIsRich($is_rich)
    {
        $this->is_rich = $is_rich;
        return $this;
    }





}