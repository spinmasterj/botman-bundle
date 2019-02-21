<?php

namespace Spinmasterj\BotManBundle\Service;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Cache\DoctrineCache;
use Doctrine\Common\Cache\FilesystemCache;
use BotMan\BotMan\BotManFactory;

class Factory
{
    private $config;
    private $cacheDir;

    /**
     * Factory constructor.
     *
     * @param array $config
     * @param string $cacheDir
     */
    public function __construct($config, string $cacheDir)
    {
        $this->config   = $config;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param array $config
     * @return BotMan
     */
    public function createBotFramework(
        $botName = '',
        $botFactory = \BotMan\BotMan\BotManFactory::class
    ): BotMan
    {
        return $this->create(\BotMan\Drivers\BotFramework\BotFrameworkDriver::class, $botName, $botFactory);
    }

    /**
     * @todo consider making this method also teach the bots its skills
     *
     * @param string $driverClass
     * @param array $config
     * @return BotMan
     */
    public function create(
        string $driverClass,
        string $botName = '',
        $botFactory = BotMan\BotMan\BotManFactory::class
    ): BotMan
    {
        DriverManager::loadDriver($driverClass);

        $cacheDriver = new DoctrineCache(new FilesystemCache($this->cacheDir));

        return $botFactory::create(
            $this->buildConfigForBotMan($botName, $driverClass),
            $cacheDriver,
            null,
            null,
            $botName
        );
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     * @return Factory
     */
    public function setCacheDir(string $cacheDir): Factory
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }

    /**
     * @param string $botName
     * @return array
     */
    protected function getConfigForBot($botName): array
    {
        return $this->config[strtolower($botName)] ?? $this->config;
    }

    /**
     * @param string $botName
     * @param string $driverClass e.g. BotMan\\Drivers\\Slack\\SlackDriver
     * @return array
     */
    protected function getDriverConfigForBot(string $botName, string $driverClass): array
    {
        $botConfig  = $this->getConfigForBot($botName);
        $driverName = $this->getDriverType($driverClass);

        return $botConfig['driver'][$driverName] ?? [];
    }

    protected function buildConfigForBotMan($botName, $driverClass)
    {
        $driverConfig = $this->getDriverConfigForBot($botName, $driverClass);

        return [
            'botman'                           => $this->config['botman'] ?? [],
            'botframework'                     => $driverConfig,
            $this->getDriverType($driverClass) => $driverConfig,
        ];
    }

    protected function getDriverType($driverClass)
    {
        $driverNamespaceArray = explode('\\', $driverClass);
        $driverClassName      = end($driverNamespaceArray);
        $driverName           = str_ireplace('driver', '', $driverClassName);

        return strtolower($driverName);
    }

}