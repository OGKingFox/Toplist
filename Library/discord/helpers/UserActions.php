<?php


class UserActions extends NexusBot {

    private $server_id;

    /**
     * @var Users
     */
    private $user;

    /**
     * UserActions constructor.
     * @param Users $user
     */
    public function __construct(Users $user) {
        global $config;
        $this->user      = $user;
        $this->server_id = $config->path("discord.server_id");
    }

    /**
     * @return mixed
     */
    public function ban() {
        $response = $this->setEndpoint("guilds/".$this->server_id."/bans/".$this->user->getUserId())
            ->setType("put")
            ->setData([
                'delete-message-days' => 7,
                'reason' => 'Ban from website.',
            ])
            ->setIsBot(true)
            ->submit();

        if (!$response) {
            $servers = Servers::getServerByOwner2($this->user->getUserId());

            if ($servers) {
                $servers->servers->delete();
                $servers->info->delete();
            }

            $this->user->setPremiumLevel(0);
            $this->user->setPremiumExpires(-1);
            $this->user->setRole("Banned");
            $this->user->update();
        } else {
            return [
                'success' => false,
                'message' => $response,
                'title' => 'Failed'
            ];
        }

        return [
            'success' => $response ? false : true,
            'message' => $this->user->getUsername().' has '.($response ? 'not ' : '').'been banned.',
            'title'   => 'Banned'
        ];
    }


    public function unban() {
        $response = $this->setEndpoint("guilds/".$this->server_id."/bans/".$this->user->getUserId())
            ->setType("delete")
            ->setIsBot(true)
            ->submit();

        if (!$response) {
            $this->user->setPremiumLevel(0);
            $this->user->setPremiumExpires(-1);
            $this->user->setRole("Member");
            $this->user->update();
            $message = $this->user->getUsername()." has been unbanned.";
        } else {
            $message = $response->message;
        }

        return [
            'success' => $response ? false : true,
            'message' => $message,
            'title'   => 'Ban Revoked'
        ];
    }

    public function kick() {
        $member = $this->getMember();

        if (isset($member->message)) {
            return [
                'success' => false,
                'message' => $member->message
            ];
        }

        $response = $this->setEndpoint("guilds/".$this->server_id."/members/".$this->user->getUserId())
            ->setType("delete")
            ->setIsBot(true)
            ->submit();

        if (!$response) {
            $this->user->setRole("Member");
            $this->user->update();
        }

        if ($response) {
            $message = $response->message;
        } else {
            $message = $member;
        }

        return [
            'success' => $response ? false : true,
            'message' => $message,
            'title'   => 'User Kicked'
        ];
    }

    public function getMember() {
        $response = $this->setEndpoint("guilds/".$this->server_id."/members/".$this->user->getUserId())
            ->setIsBot(true)
            ->submit();

        return $response;
    }


}