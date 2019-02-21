<?php

namespace Spinmasterj\BotManBundle\Skills\BotMan;

use BotMan\BotMan\BotMan;
use Spinmasterj\BotManBundle\Skills\Skill;

/**
 * Class HelloWorld
 *
 * @package Spinmasterj\BotManBundle\Skills\BotMan
 */
class HelloWorld extends Skill
{
    /**
     * @inheritdoc
     */
    public function teach(BotMan $botMan): array
    {
        $botMan->hears('.*', function(BotMan $botMan) {
            $botMan->reply('Hello World!');
        });
    }

}