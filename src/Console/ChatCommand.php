<?php

namespace Spinmasterj\BotManBundle\Console;

use BotMan\BotMan\BotMan;
use Spinmasterj\BotManBundle\Drivers\ConsoleDriver;
use Spinmasterj\BotManBundle\Service\Factory;
use Spinmasterj\BotManBundle\Service\SkillFinder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ChatCommand
 *
 * @package Spinmasterj\BotManBundle\Console
 */
class ChatCommand extends Command
{
    const ARG_BOT_NAME = 'botName';

    /**
     * @var string
     */
    protected static $defaultName = 'bot:chat';

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var SkillFinder
     */
    protected $skillFinder;

    /**
     * @inheritdoc
     */
    public function __construct(Factory $factory, SkillFinder $skillFinder)
    {
        $this->factory     = $factory;
        $this->skillFinder = $skillFinder;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Chat with your custom bot')
            ->addArgument(self::ARG_BOT_NAME, InputArgument::REQUIRED, 'Name of Bot to chat with.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $driver = new ConsoleDriver($input, $output);
        $driver->setBotName(ucfirst($input->getArgument(self::ARG_BOT_NAME)));

        $botMan = $this->factory->createBotFramework();
        $botMan->setDriver($driver);

        $this->skillFinder->teach($botMan, $input->getArgument(self::ARG_BOT_NAME));

        while (true) {
            $answer = $io->ask('You: ');
            $driver->setMessage($answer);

            if (in_array($driver, ['quit', 'exit'])) {
                return 0;
            }

            $botMan->listen();
        }
    }
}
