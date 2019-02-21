<?php

namespace Spinmasterj\BotManBundle\BotMan\Messages;

use BotMan\BotMan\Messages\Matcher as MatcherNative;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Interfaces\Middleware\Matching;
use Illuminate\Support\Collection;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

/**
 * Class Matcher
 *
 * @package Spinmasterj\BotManBundle\BotMan\Messages
 */
class Matcher extends MatcherNative
{
    /**
     * The original regex matches {2}, {2days} etc, which will cause regex errors
     *
     * @var string
     */
    const PARAM_NAME_REGEX = '`\{((?:(?!\d[^}]*)\w)+?)\}`';

    /**
     * Overridden to fix the PARAM_NAME_REGEX problem
     *
     * @param IncomingMessage $message
     * @param Answer $answer
     * @param string $pattern
     * @param Matching[] $middleware
     * @return int
     */
    public function isPatternValid(IncomingMessage $message, Answer $answer, $pattern, $middleware = [])
    {
        $this->matches = [];

        $answerText = $answer->getValue();
        if (is_array($answerText)) {
            $answerText = '';
        }

        $pattern = str_replace('/', '\/', $pattern);
        $text = '/^'.preg_replace(self::PARAM_NAME_REGEX, '(?<$1>.*)', $pattern).' ?$/miu';

        $regexMatched = (bool) preg_match($text, $message->getText(), $this->matches) || (bool) preg_match($text, $answerText, $this->matches);

        // Try middleware first
        if (count($middleware)) {
            return Collection::make($middleware)->reject(function (Matching $middleware) use (
                    $message,
                    $pattern,
                    $regexMatched
                ) {
                    return $middleware->matching($message, $pattern, $regexMatched);
                })->isEmpty() === true;
        }

        return $regexMatched;
    }
}