<?php

namespace Spinmasterj\BotManBundle\BotMan;

use BotMan\BotMan\BotMan as BotManNative;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Spinmasterj\BotManBundle\BotMan\Messages\Matcher;
use BotMan\BotMan\Interfaces\CacheInterface;
use BotMan\BotMan\Interfaces\DriverInterface;
use BotMan\BotMan\Interfaces\StorageInterface;

class BotMan extends BotManNative
{
    /**
     * @var string
     */
    protected $name = 'BotMan';

    /**
     * I don't know why $this->cache is private, but it's super lame.
     *
     * @var CacheInterface
     */
    protected $localCache;

    /**
     * Overridden to implement custom matcher and gain access to private cache.
     *
     * @param CacheInterface $cache
     * @param DriverInterface $driver
     * @param array $config
     * @param StorageInterface $storage
     */
    public function __construct(CacheInterface $cache, DriverInterface $driver, $config, StorageInterface $storage)
    {
        parent::__construct($cache, $driver, $config, $storage);

        $this->localCache = $cache;
        $this->matcher = new Matcher();
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        if ($user = $this->localCache->get('user_' . $this->driver->getName() . '_' . $this->getMessage()->getSender())) {
            return $user;
        }

        $user = $this->getDriver()->getUser($this->getMessage());
        $this->localCache->put('user_' . $this->driver->getName() . '_' . $user->getId(), $user,
            $this->config['user_cache_time'] ?? 30);

        return $user;
    }


    /**
     * @param string $id
     * @return \BotMan\BotMan\Interfaces\UserInterface|null
     */
    public function getUserById(string $id)
    {
        if ($this->isUserId($id)) {

            if ($user = $this->localCache->get('user_' . $this->driver->getName() . '_' . $id)) {
                return $user;
            }

            $user = $this->getDriver()->getUser(new IncomingMessage(null, $id, null));
            if ($user->getId()) {
                $this->localCache->put('user_' . $this->driver->getName() . '_' . $user->getId(), $user,
                    $this->config['user_cache_time'] ?? 30);
            }

            return $user;
        }

        return null;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isUserId(string $id): bool
    {
        return preg_match('`^(<@[A-Z0-9]*)>$`i', $subject);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): BotMan
    {
        $this->name = $name;

        return $this;
    }
}