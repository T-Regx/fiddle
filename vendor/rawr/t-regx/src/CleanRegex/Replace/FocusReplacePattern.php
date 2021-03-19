<?php
namespace TRegx\CleanRegex\Replace;

use TRegx\CleanRegex\Exception\FocusGroupNotMatchedException;
use TRegx\CleanRegex\Exception\MissingReplacementKeyException;
use TRegx\CleanRegex\Internal\InternalPattern as Pattern;
use TRegx\CleanRegex\Internal\Match\Base\ApiBase;
use TRegx\CleanRegex\Internal\Match\UserData;
use TRegx\CleanRegex\Internal\Replace\By\GroupFallbackReplacer;
use TRegx\CleanRegex\Internal\Replace\By\GroupMapper\FocusWrapper;
use TRegx\CleanRegex\Internal\Replace\By\NonReplaced\DefaultStrategy;
use TRegx\CleanRegex\Internal\Replace\By\NonReplaced\LazyMessageThrowStrategy;
use TRegx\CleanRegex\Internal\Replace\By\PerformanceEmptyGroupReplace;
use TRegx\CleanRegex\Internal\Replace\Counting\CountingStrategy;
use TRegx\CleanRegex\Internal\Replace\ReferencesReplacer;
use TRegx\CleanRegex\Internal\Subject;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Replace\By\ByReplacePattern;
use TRegx\CleanRegex\Replace\By\ByReplacePatternImpl;
use TRegx\CleanRegex\Replace\Callback\ReplacePatternCallbackInvoker;

class FocusReplacePattern implements SpecificReplacePattern
{
    /** @var SpecificReplacePattern */
    private $replacePattern;
    /** @var Pattern */
    private $pattern;
    /** @var string */
    private $subject;
    /** @var int */
    private $limit;
    /** @var string|int */
    private $nameOrIndex;
    /** @var CountingStrategy */
    private $countingStrategy;

    public function __construct(SpecificReplacePattern $replacePattern, Pattern $pattern, string $subject, int $limit, $nameOrIndex, CountingStrategy $countingStrategy)
    {
        $this->replacePattern = $replacePattern;
        $this->pattern = $pattern;
        $this->subject = $subject;
        $this->limit = $limit;
        $this->nameOrIndex = $nameOrIndex;
        $this->countingStrategy = $countingStrategy;
    }

    public function with(string $replacement): string
    {
        return $this->replacePattern->callback(function (Detail $detail) use ($replacement) {
            if (!$detail->matched($this->nameOrIndex)) {
                throw new FocusGroupNotMatchedException($detail->subject(), $this->nameOrIndex);
            }
            return $detail->group($this->nameOrIndex)->substitute($replacement);
        });
    }

    public function withReferences(string $replacement): string
    {
        return $this->replacePattern->callback(function (Detail $detail) use ($replacement) {
            if (!$detail->matched($this->nameOrIndex)) {
                throw new FocusGroupNotMatchedException($detail->subject(), $this->nameOrIndex);
            }
            $group = $detail->group($this->nameOrIndex);
            return $group->substitute(ReferencesReplacer::replace($replacement, \array_merge(
                    [$group->text()],
                    $detail->groups()->texts())
            ));
        });
    }

    public function callback(callable $callback): string
    {
        return $this->replacePattern->callback(function (Detail $detail) use ($callback) {
            if ($detail->matched($this->nameOrIndex)) {
                return $detail->group($this->nameOrIndex)->substitute($callback($detail));
            }
            throw new FocusGroupNotMatchedException($detail->subject(), $this->nameOrIndex);
        });
    }

    public function by(): ByReplacePattern
    {
        return new ByReplacePatternImpl(
            new GroupFallbackReplacer(
                $this->pattern,
                new Subject($this->subject),
                $this->limit,
                new DefaultStrategy(),
                $this->countingStrategy,
                new ApiBase($this->pattern, $this->subject, new UserData())),
            new LazyMessageThrowStrategy(MissingReplacementKeyException::class),
            new PerformanceEmptyGroupReplace($this->pattern, new Subject($this->subject), $this->limit),
            new ReplacePatternCallbackInvoker($this->pattern, new Subject($this->subject), $this->limit, new DefaultStrategy(), $this->countingStrategy),
            $this->subject,
            new FocusWrapper($this->nameOrIndex));
    }
}
