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

class ConsoleDriver implements DriverInterface
{
    const DRIVER_NAME = 'Console';

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var Output
     */
    protected $output;

    /**
     * @var string
     */
    protected $botName = 'BotMan';

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $botId;

    /**
     * @var bool
     */
    protected $hasQuestion = false;

    /**
     * @var array
     */
    protected $lastQuestions;

    /**
     * Driver constructor.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
    }

    /**
     * Return the driver name.
     *
     * @return string
     */
    public function getName()
    {
        return self::DRIVER_NAME;
    }

    /**
     * Determine if the request is for this driver.
     *
     * @return bool
     */
    public function matchesRequest()
    {
        return false;
    }

    /**
     * @param  IncomingMessage $message
     * @return Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        $index = (int)$message->getText() - 1;

        if ($this->hasQuestion && isset($this->lastQuestions[$index])) {
            $question = $this->lastQuestions[$index];

            return Answer::create($question['name'])
                ->setInteractiveReply(true)
                ->setValue($question['value'])
                ->setMessage($message);
        }

        return Answer::create($this->message)->setMessage($message);
    }

    /**
     * Retrieve the chat message.
     *
     * @return array
     */
    public function getMessages()
    {
        return [new IncomingMessage($this->message, 999, '#channel', $this->message)];
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        return strpos($this->message, $this->getBotNameString()) === 0;
    }

    /**
     * Send a typing indicator.
     *
     * @param IncomingMessage $matchingMessage
     * @return mixed
     */
    public function types(IncomingMessage $matchingMessage)
    {
        $this->output->writeln($this->getBotNameString() . '...');
    }

    /**
     * Retrieve User information.
     *
     * @param IncomingMessage $matchingMessage
     * @return User
     */
    public function getUser(IncomingMessage $matchingMessage)
    {
        return new User($matchingMessage->getSender());
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return false;
    }

    /**
     * @param string|\BotMan\BotMan\Messages\Outgoing\Question $message
     * @param IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return $this
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $questionData = null;
        if ($message instanceof OutgoingMessage) {
            $text = $message->getText();
        } elseif ($message instanceof Question) {
            $text         = $message->getText();
            $questionData = $message->toArray();
        } else {
            $text = $message;
        }

        return compact('text', 'questionData');
    }

    /**
     * @param mixed $payload
     */
    public function sendPayload($payload)
    {
        $questionData = $payload['questionData'];
        $this->output->writeln($this->getBotName() . ': ' . $payload['text']);

        if (!is_null($questionData)) {
            foreach ($questionData['actions'] as $key => $action) {
                $this->output->writeln(($key + 1) . ') ' . $action['text']);
            }
            $this->hasQuestion   = true;
            $this->lastQuestions = $questionData['actions'];
        }
    }

    /**
     * Does the driver match to an incoming messaging service event.
     *
     * @return bool
     */
    public function hasMatchingEvent(): bool
    {
        return false;
    }

    /**
     * Tells if the stored conversation callbacks are serialized.
     *
     * @return bool
     */
    public function serializesCallbacks(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getBotName(): string
    {
        return $this->botName;
    }

    /**
     * @param string $botName
     */
    public function setBotName(string $botName): void
    {
        $this->botName = $botName;
    }

    /**
     * @return string
     */
    public function getBotNameString(): string
    {
        return $this->getBotName() . ": ";
    }
}
