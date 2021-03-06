<?php
namespace TRegx\CleanRegex\Composite;

use TRegx\CleanRegex\Internal\InternalPattern as Pattern;
use TRegx\CleanRegex\Internal\Replace\By\NonReplaced\SubjectRs;
use TRegx\CleanRegex\Internal\Replace\Counting\IgnoreCounting;
use TRegx\CleanRegex\Internal\Subject;
use TRegx\CleanRegex\Replace\Callback\MatchStrategy;
use TRegx\CleanRegex\Replace\Callback\ReplacePatternCallbackInvoker;
use TRegx\CleanRegex\Replace\ReplaceReferences;
use TRegx\SafeRegex\preg;

class ChainedReplace
{
    /** @var Pattern[] */
    private $patterns;
    /** @var string */
    private $subject;
    /** @var SubjectRs */
    private $substitute;

    public function __construct(array $patterns, string $subject, SubjectRs $substitute)
    {
        $this->patterns = $patterns;
        $this->subject = $subject;
        $this->substitute = $substitute;
    }

    public function with(string $replacement): string
    {
        return $this->withReferences(ReplaceReferences::quote($replacement));
    }

    public function withReferences(string $replacement): string
    {
        $subject = $this->subject;
        foreach ($this->patterns as $pattern) {
            $subject = preg::replace($pattern->pattern, $replacement, $subject);
        }
        return $subject;
    }

    public function callback(callable $callback): string
    {
        $subject = $this->subject;
        foreach ($this->patterns as $pattern) {
            $subject = $this->replaceNext($pattern, $subject, $callback);
        }
        return $subject;
    }

    private function replaceNext(Pattern $pattern, string $subject, callable $callback): string
    {
        $invoker = new ReplacePatternCallbackInvoker($pattern, new Subject($subject), -1, $this->substitute, new IgnoreCounting());
        return $invoker->invoke($callback, new MatchStrategy());
    }
}
