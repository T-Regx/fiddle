<?php
namespace TRegx\CleanRegex\Internal\Match\Stream;

use TRegx\CleanRegex\Internal\Match\MatchAll\MatchAllFactory;
use TRegx\CleanRegex\Internal\Match\UserData;
use TRegx\CleanRegex\Internal\Model\MatchObjectFactory;
use TRegx\CleanRegex\Internal\Subjectable;
use TRegx\CleanRegex\Match\Details\Match;

class MatchStream implements Stream
{
    /** @var BaseStream */
    private $stream;
    /** @var Subjectable */
    private $subjectable;
    /** @var UserData */
    private $userData;
    /** @var MatchAllFactory */
    private $allFactory;

    public function __construct(BaseStream $stream, Subjectable $subjectable, UserData $userData, MatchAllFactory $allFactory)
    {
        $this->stream = $stream;
        $this->subjectable = $subjectable;
        $this->userData = $userData;
        $this->allFactory = $allFactory;
    }

    public function all(): array
    {
        return $this->stream->all()->getMatchObjects($this->factory(-1));
    }

    public function first(): Match
    {
        return $this->factory(1)->create(0, $this->stream->first(), $this->allFactory);
    }

    private function factory(int $limit): MatchObjectFactory
    {
        return new MatchObjectFactory($this->subjectable, $limit, $this->userData);
    }

    public function firstKey(): int
    {
        return $this->stream->firstKey();
    }
}
