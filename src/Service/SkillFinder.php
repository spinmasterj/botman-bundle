<?php

namespace Spinmasterj\BotManBundle\Service;

use BotMan\BotMan\BotMan;
use Spinmasterj\BotManBundle\Skills\Help;
use Spinmasterj\BotManBundle\Skills\Skill;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class SkillFinder
 *
 * @package App\Skills
 */
class SkillFinder
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $config;

    /**
     * SkillFinder constructor
     *
     * @param array $config
     * @param Container $container
     */
    public function __construct(array $config, Container $container)
    {
        $this->config = $config;
        $this->setContainer($container);
    }

    /**
     * @param BotMan $botMan
     * @param $botName
     */
    public function teach(BotMan $botMan, $botName, $botId = null)
    {
        /** @var Skill $skill */
        foreach ($this->find($botMan, ucfirst($botName), $botId) as $skill) {
            $skill->teach($botMan);
        }
    }

    /**
     * @param BotMan $botMan
     * @param string $botName
     * @return array
     */
    public function find(BotMan $botMan, $botName, $botId = null): array
    {
        $botConfig = $this->getConfigForBot($botName);

        $skillLocation    = $botConfig['skill_location'] ?? '../Skills/BotMan';
        $skillNamespace   = $botConfig['skill_namespace'] ?? 'Spinmasterj\\BotManBundle\\Skills\\BotMan';
        $skillsAsServices = $this->config['skills_as_services'] ?? false;

        $finder = new Finder();
        $finder->files()
            ->in($skillLocation)
            ->sortByName();

        $skills = [];
        foreach ($finder as $fileInfo) {
            $skills[] = $this->createSkill(
                $skillsAsServices,
                $skillNamespace,
                $fileInfo,
                $botName,
                $botId
            );
        }

        $skills[] = (new Help($skills))
            ->setBotName($botName)
            ->setBotId($botId);

        return $skills;
    }

    /**
     * @param bool $skillsAsServices
     * @param string $className
     * @param SplFileInfo $fileInfo
     * @param string $botName
     * @param null $botId
     * @return Skill
     */
    protected function createSkill(
        bool $skillsAsServices,
        string $skillNamespace,
        SplFileInfo $fileInfo,
        string $botName,
        $botId = null
    ): Skill
    {
        /** @var Skill $skill */
        if ($skillsAsServices) {
            $skill = $this->container->get($skillNamespace . '\\' . $fileInfo->getBasename('.php'));
        } else {
            $skillName = $skillNamespace . '\\' . $fileInfo->getBasename('.php');
            require_once($fileInfo->getPathname());
            $skill = (new $skillName());
        }
        $skill = $skill->setBotName($botName)
            ->setBotId($botId);

        return $skill;
    }

    /**
     * @param string $botName
     * @return array
     */
    protected function getConfigForBot($botName)
    {
        return $this->config[strtolower($botName)] ?? [];
    }
}