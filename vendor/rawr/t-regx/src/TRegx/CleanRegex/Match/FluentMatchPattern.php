<?php
namespace TRegx\CleanRegex\Match;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use TRegx\CleanRegex\Exception\NoSuchElementFluentException;
use TRegx\CleanRegex\Internal\Exception\Messages\NthFluentMessage;
use TRegx\CleanRegex\Internal\Exception\NoFirstStreamException;
use TRegx\CleanRegex\Internal\Factory\FluentOptionalWorker;
use TRegx\CleanRegex\Internal\Factory\SecondLevelFluentOptionalWorker;
use TRegx\CleanRegex\Internal\Match\FindFirst\EmptyOptional;
use TRegx\CleanRegex\Internal\Match\FindFirst\OptionalImpl;
use TRegx\CleanRegex\Internal\Match\FluentInteger;
use TRegx\CleanRegex\Internal\Match\Stream\ArrayOnlyStream;
use TRegx\CleanRegex\Internal\Match\Stream\FlatMappingStream;
use TRegx\CleanRegex\Internal\Match\Stream\FromArrayStream;
use TRegx\CleanRegex\Internal\Match\Stream\GroupByCallbackStream;
use TRegx\CleanRegex\Internal\Match\Stream\KeysStream;
use TRegx\CleanRegex\Internal\Match\Stream\MappingStream;
use TRegx\CleanRegex\Internal\Match\Stream\Stream;

class FluentMatchPattern implements MatchPatternInterface
{
    /** @var Stream */
    private $stream;
    /** @var FluentOptionalWorker */
    private $firstWorker;

    public function __construct(Stream $stream, FluentOptionalWorker $firstWorker)
    {
        $this->stream = $stream;
        $this->firstWorker = $firstWorker;
    }

    public function all(): array
    {
        return $this->stream->all();
    }

    public function only(int $limit): array
    {
        if ($limit < 0) {
            throw new InvalidArgumentException("Negative limit: $limit");
        }
        return \array_slice($this->stream->all(), 0, $limit);
    }

    /**
     * @param callable|null $consumer
     * @return string|mixed
     */
    public function first(callable $consumer = null)
    {
        try {
            $firstElement = $this->stream->first();
            return $consumer ? $consumer($firstElement) : $firstElement;
        } catch (NoFirstStreamException $exception) {
            throw $this->firstWorker->noFirstElementException();
        }
    }

    public function findFirst(callable $consumer): Optional
    {
        try {
            return new OptionalImpl($consumer($this->stream->first()));
        } catch (NoFirstStreamException $exception) {
            return new EmptyOptional($this->firstWorker, NoSuchElementFluentException::class);
        }
    }

    public function nth(int $index)
    {
        return $this->findNth($index)->orThrow();
    }

    public function findNth(int $index): Optional
    {
        if ($index < 0) {
            throw new InvalidArgumentException("Negative index: $index");
        }
        $elements = \array_values($this->stream->all());
        if (\array_key_exists($index, $elements)) {
            return new OptionalImpl($elements[$index]);
        }
        return new EmptyOptional(
            new SecondLevelFluentOptionalWorker(new NthFluentMessage($index, \count($elements))),
            NoSuchElementFluentException::class);
    }

    public function forEach(callable $consumer): void
    {
        foreach ($this->stream->all() as $key => $value) {
            $consumer($value, $key);
        }
    }

    public function count(): int
    {
        return \count($this->stream->all());
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->stream->all());
    }

    public function map(callable $mapper): FluentMatchPattern
    {
        return $this->next(new MappingStream($this->stream, $mapper));
    }

    public function flatMap(callable $mapper): FluentMatchPattern
    {
        return $this->next(new FlatMappingStream($this->stream, $mapper));
    }

    public function distinct(): FluentMatchPattern
    {
        return $this->next(new ArrayOnlyStream($this->stream, '\array_unique'));
    }

    public function filter(callable $predicate): FluentMatchPattern
    {
        return $this->next(new FromArrayStream(\array_values(\array_filter($this->stream->all(), $predicate))));
    }

    public function values(): FluentMatchPattern
    {
        return $this->next(new ArrayOnlyStream($this->stream, '\array_values'));
    }

    public function keys(): FluentMatchPattern
    {
        return $this->next(new KeysStream($this->stream));
    }

    public function asInt(): FluentMatchPattern
    {
        return $this->map([FluentInteger::class, 'parse']);
    }

    public function groupByCallback(callable $groupMapper): FluentMatchPattern
    {
        return $this->next(new GroupByCallbackStream($this->stream, $groupMapper));
    }

    private function next(Stream $stream): FluentMatchPattern
    {
        return new FluentMatchPattern($stream, $this->firstWorker);
    }
}
