<?php


class UserActions extends NexusBot {

    /**
     * @var Users
     */
    private $user;

    /**
     * UserActions constructor.
     * @param Users $user
     */
    public function __construct(Users $user) {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function ban() {
        $response = $this->setEndpoint("guilds/".server_id."/bans/".$this->user->getUserId())
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
        }

        return [
            'success' => $response ? false : true,
            'message' => $this->user->getUsername().' has '.($response ? 'not ' : '').'been banned.'
        ];
    }


    public function unban() {
        $response = $this->setEndpoint("guilds/".server_id."/bans/".$this->user->getUserId())
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
            'message' => $message
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

        $response = $this->setEndpoint("guilds/".server_id."/members/".$this->user->getUserId())
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
            'message' => $message
        ];
    }

    public function getMember() {
        $response = $this->setEndpoint("guilds/".server_id."/members/".$this->user->getUserId())
            ->setIsBot(true)
            ->submit();

        return $response;
    }


}