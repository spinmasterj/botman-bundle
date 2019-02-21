<?php

namespace Spinmasterj\BotManBundle\Skills;

use BotMan\BotMan\BotMan;

/**
 * Class Skill
 *
 * @package Spinmasterj\BotManBundle\Skills
 */
abstract class Skill
{
    /**
     * Help strings to be populated in child classes
     *
     * @var string[]
     */
    protected $help = [];

    /**
     * @var string
     */
    protected $botName;

    /**
     * @var string
     */
    protected $botId;

    /**
     * @param BotMan $botMan
     */
    abstract public function teach(BotMan $botMan);

    /**
     * @return string
     */
    public function getBotName(): string
    {
        return $this->botName;
    }

    /**
     * @param string $botName
     * @return Skill
     */
    public function setBotName($botName): Skill
    {
        $this->botName = $botName;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getHelp(): array
    {
        return $this->help;
    }

    /**
     * @param string $helpString
     * @return Skill
     */
    public function appendHelp(string $helpString): Skill
    {
        $this->help[] = $helpString;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBotId()
    {
        return $this->botId;
    }

    /**
     * @param string|null $botId
     */
    public function setBotId($botId): Skill
    {
        $this->botId = $botId;

        return $this;
    }

    /**
     * @return string
     */
    public function getBotIdRegex()
    {
        $botId = '';

        if ($this->getBotId()) {
            $botId = '|' . $this->getBotId();
        }

        return "({$this->getBotName()}$botId)";
    }
}