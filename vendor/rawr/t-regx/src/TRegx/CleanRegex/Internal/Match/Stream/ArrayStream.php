<?php
namespace TRegx\CleanRegex\Internal\Match\Stream;

use TRegx\CleanRegex\Internal\Exception\NoFirstStreamException;

class ArrayStream implements Stream
{
    /** @var array */
    private $elements;

    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function all(): array
    {
        return $this->elements;
    }

    public function first()
    {
        if (\count($this->elements) === 0) {
            throw new NoFirstStreamException();
        }
        return \reset($this->elements);
    }

    public function firstKey()
    {
        \reset($this->elements);
        return \key($this->elements);
    }
}