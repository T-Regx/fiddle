<?php
namespace TRegx\CleanRegex\Match\Details\Group;

use TRegx\CleanRegex\Exception\GroupNotMatchedException;
use TRegx\CleanRegex\Internal\Factory\GroupExceptionFactory;
use TRegx\CleanRegex\Internal\Factory\NotMatchedOptionalWorker;
use TRegx\CleanRegex\Internal\Match\Details\Group\GroupDetails;

class NotMatchedGroup implements DetailGroup, MatchGroup
{
    /** @var GroupDetails */
    private $details;
    /** @var GroupExceptionFactory */
    private $exceptionFactory;
    /** @var NotMatchedOptionalWorker */
    private $optionalWorker;
    /** @var string */
    private $subject;

    public function __construct(GroupDetails $details,
                                GroupExceptionFactory $exceptionFactory,
                                NotMatchedOptionalWorker $optionalWorker,
                                string $subject)
    {
        $this->details = $details;
        $this->exceptionFactory = $exceptionFactory;
        $this->optionalWorker = $optionalWorker;
        $this->subject = $subject;
    }

    public function text(): string
    {
        throw $this->groupNotMatched('text');
    }

    public function textLength(): int
    {
        throw $this->groupNotMatched('textLength');
    }

    public function textByteLength(): int
    {
        throw $this->groupNotMatched('textByteLength');
    }

    public function toInt(): int
    {
        throw $this->groupNotMatched('toInt');
    }

    public function isInt(): bool
    {
        throw $this->groupNotMatched('isInt');
    }

    protected function groupNotMatched(string $method): GroupNotMatchedException
    {
        return $this->exceptionFactory->create($method);
    }

    public function matched(): bool
    {
        return false;
    }

    public function equals(string $expected): bool
    {
        return false;
    }

    public function name(): ?string
    {
        return $this->details->name;
    }

    public function index(): int
    {
        return $this->details->index;
    }

    /**
     * @return int|string
     */
    public function usedIdentifier()
    {
        return $this->details->nameOrIndex;
    }

    public function offset(): int
    {
        throw $this->groupNotMatched('offset');
    }

    public function tail(): int
    {
        throw $this->groupNotMatched('tail');
    }

    public function byteOffset(): int
    {
        throw $this->groupNotMatched('byteOffset');
    }

    public function byteTail(): int
    {
        throw $this->groupNotMatched('byteTail');
    }

    public function substitute(string $replacement): string
    {
        throw $this->groupNotMatched('substitute');
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function all(): array
    {
        return $this->details->all();
    }

    public function orReturn($substitute)
    {
        return $substitute;
    }

    public function orThrow(string $exceptionClassName = null): void
    {
        throw $this->optionalWorker->orThrow($exceptionClassName ?? GroupNotMatchedException::class);
    }

    public function orElse(callable $substituteProducer)
    {
        return $this->optionalWorker->orElse($substituteProducer);
    }
}
