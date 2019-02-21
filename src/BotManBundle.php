<?php

namespace Spinmasterj\BotManBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Spinmasterj\BotManBundle\DependencyInjection\SpinmasterjBotManExtension;

/**
 * Class BotManBundle
 *
 * @package Spinmasterj\BotManBundle
 */
class BotManBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SpinmasterjBotManExtension();
    }
}