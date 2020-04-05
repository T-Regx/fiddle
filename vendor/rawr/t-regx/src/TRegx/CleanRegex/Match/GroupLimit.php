<?php
namespace TRegx\CleanRegex\Match;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use TRegx\CleanRegex\Internal\Exception\Messages\NoFirstElementFluentMessage;
use TRegx\CleanRegex\Internal\Factory\NotMatchedFluentOptionalWorker;
use TRegx\CleanRegex\Internal\GroupLimit\GroupLimitAll;
use TRegx\CleanRegex\Internal\GroupLimit\GroupLimitFindFirst;
use TRegx\CleanRegex\Internal\GroupLimit\GroupLimitFirst;
use TRegx\CleanRegex\Internal\Match\Base\Base;
use TRegx\CleanRegex\Internal\Match\Details\Group\GroupFacade;
use TRegx\CleanRegex\Internal\Match\Details\Group\MatchGroupFactoryStrategy;
use TRegx\CleanRegex\Internal\Match\FlatMapper;
use TRegx\CleanRegex\Internal\Match\MatchAll\EagerMatchAllFactory;
use TRegx\CleanRegex\Internal\Match\MatchAll\LazyMatchAllFactory;
use TRegx\CleanRegex\Internal\Match\Stream\BaseStream;
use TRegx\CleanRegex\Internal\Match\Stream\MatchGroupStream;
use TRegx\CleanRegex\Internal\Match\Stream\Stream;
use TRegx\CleanRegex\Internal\Model\Match\RawMatchOffset;
use TRegx\CleanRegex\Internal\Model\Matches\RawMatchesOffset;
use TRegx\CleanRegex\Internal\PatternLimit;
use TRegx\CleanRegex\Match\Details\Group\MatchGroup;
use TRegx\CleanRegex\Match\FindFirst\Optional;
use TRegx\CleanRegex\Match\Offset\OffsetLimit;

class GroupLimit implements PatternLimit
{
    /** @var GroupLimitAll */
    private $allFactory;
    /** @var GroupLimitFirst */
    private $firstFactory;
    /** @var GroupLimitFindFirst */
    private $findFirstFactory;

    /** @var Base */
    private $base;
    /** @var string|int */
    private $nameOrIndex;
    /** @var OffsetLimit */
    private $offsetLimit;

    public function __construct(Base $base, $nameOrIndex, OffsetLimit $offsetLimit)
    {
        $this->allFactory = new GroupLimitAll($base, $nameOrIndex);
        $this->firstFactory = new GroupLimitFirst($base, $nameOrIndex);
        $this->findFirstFactory = new GroupLimitFindFirst($base, $nameOrIndex);
        $this->base = $base;
        $this->nameOrIndex = $nameOrIndex;
        $this->offsetLimit = $offsetLimit;
    }

    /**
     * @param callable|null $consumer
     * @return string|mixed
     */
    public function first(callable $consumer = null)
    {
        $first = $this->firstFactory->getFirstForGroup();
        if ($consumer === null) {
            return $first->getGroup($this->nameOrIndex);
        }
        return $consumer($this->matchGroupDetails($first));
    }

    private function matchGroupDetails(RawMatchOffset $first): MatchGroup
    {
        $facade = new GroupFacade($first, $this->base, $this->nameOrIndex, new MatchGroupFactoryStrategy(), new LazyMatchAllFactory($this->base));
        return $facade->createGroup($first);
    }

    public function findFirst(callable $consumer): Optional
    {
        return $this->findFirstFactory->getOptionalForGroup($consumer);
    }

    /**
     * @return (string|null)[]
     */
    public function all(): array
    {
        return $this->allFactory->getAllForGroup()->getGroupTexts($this->nameOrIndex);
    }

    /**
     * @param int $limit
     * @return (string|null)[]
     */
    public function only(int $limit): array
    {
        $matches = $this->allFactory->getAllForGroup();
        if ($limit < 0) {
            throw new InvalidArgumentException("Negative limit: $limit");
        }
        return \array_slice($matches->getGroupTexts($this->nameOrIndex), 0, $limit);
    }

    public function iterator(): Iterator
    {
        return new ArrayIterator($this->all());
    }

    /**
     * @param callable $mapper
     * @return mixed[]
     */
    public function map(callable $mapper): array
    {
        return \array_map($mapper, $this->stream()->all());
    }

    /**
     * @param callable $mapper
     * @return mixed[]
     */
    public function flatMap(callable $mapper): array
    {
        return (new FlatMapper($this->stream()->all(), $mapper))->get();
    }

    public function forEach(callable $consumer): void
    {
        foreach ($this->stream()->all() as $group) {
            $consumer($group);
        }
    }

    public function offsets(): OffsetLimit
    {
        return $this->offsetLimit;
    }

    public function fluent(): FluentMatchPattern
    {
        return new FluentMatchPattern(
            $this->stream(),
            new NotMatchedFluentOptionalWorker(new NoFirstElementFluentMessage(), $this->base->getSubject()));
    }

    private function stream(): Stream
    {
        return new MatchGroupStream(new BaseStream($this->base), $this->base, $this->nameOrIndex, new EagerMatchAllFactory(new RawMatchesOffset([])));
    }
}
