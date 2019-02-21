<?php

namespace Spinmasterj\BotManBundle\BotMan;

use BotMan\BotMan\BotManFactory as BotManFactoryNative;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Interfaces\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use BotMan\BotMan\Interfaces\StorageInterface;
use BotMan\BotMan\Storages\Drivers\FileStorage;
use BotMan\BotMan\Cache\ArrayCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Http\Curl;

/**
 * Class BotManFactory
 *
 * @package Spinmasterj\BotManBundle\BotMan
 */
class BotManFactory extends BotManFactoryNative
{
    /**
     * Create a new BotMan instance.
     *
     * @param array $config
     * @param CacheInterface $cache
     * @param Request $request
     * @param StorageInterface $storageDriver
     * @return \BotMan\BotMan\BotMan
     */
    public static function create(
        array $config,
        CacheInterface $cache = null,
        Request $request = null,
        StorageInterface $storageDriver = null,
        string $botName = ''
    )
    {
        if (empty($cache)) {
            $cache = new ArrayCache();
        }
        if (empty($request)) {
            $request = Request::createFromGlobals();
        }
        if (empty($storageDriver)) {
            $storageDriver = new FileStorage(__DIR__);
        }

        $driverManager = new DriverManager($config, new Curl());
        $driver        = $driverManager->getMatchingDriver($request);

        return (new BotMan($cache, $driver, $config, $storageDriver))
            ->setName($botName);
    }
}