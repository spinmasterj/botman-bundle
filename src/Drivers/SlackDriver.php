<?php

namespace Spinmasterj\BotManBundle\Drivers;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Users\User;
use BotMan\BotMan\Interfaces\DriverInterface;
use BotMan\BotMan\Messages\Outgoing\Question;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Symfony\Component\Console\Output\OutputInterface;
use BotMan\Drivers\Slack\SlackDriver as NativeSlackDriver;

/**
 * Class SlackDriver
 *
 * @package Spinmasterj\BotManBundle\Drivers
 */
class SlackDriver extends NativeSlackDriver
{
    /**
     * @param string $id
     * @return User
     */
    public function getUserById(string $id): User
    {
        $response = $this->sendRequest('users.info', [
            'user' => $id,
        ], new IncomingMessage(null, null, null));
        try {
            $content = json_decode($response->getContent(), true);

            return new User(null, $content['user']);
        } catch (\Exception $e) {
            return new User(null, ['id' => $matchingMessage->getSender()]);
        }
    }
}