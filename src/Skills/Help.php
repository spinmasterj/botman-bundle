<?php

namespace Spinmasterj\BotManBundle\Skills;

use BotMan\BotMan\BotMan;
use Spinmasterj\BotManBundle\Service\SkillFinder;
use Spinmasterj\BotManBundle\Skills\Skill;

class Help extends Skill
{
    /**
     * @var Skill[]
     */
    protected $skills;

    public function __construct(array $skills)
    {
        $this->skills = $skills;
    }

    /**
     * @inheritdoc
     */
    public function teach(BotMan $botMan)
    {
        $botMan->hears($this->getBotIdRegex() . '\s+help.*', function (BotMan $botMan) {
            $help = [];

            /** @var Skill $skill */
            foreach ($this->skills as $skill) {
//                $prefixedHelp = array_map(function ($helpLine) use ($skill) {
//                    return get_class_($skill) . ': ' . $helpLine;
//                }, $skill->getHelp());
//
//                $help += $prefixedHelp;

                $help += $skill->getHelp();
            }

            if (empty($help)) {
                $help = [
                    'Sorry, no skills have help configured.',
                ];
            }

            // @todo format help
            $botMan->reply(
                implode("\n", $help)
            );
        });
    }

    /**
     * @return Skill[]
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param Skill[] $skills
     */
    public function setSkills(array $skills): Help
    {
        $this->skills = $skills;

        return $this;
    }
}