<?php
namespace TRegx\CleanRegex\Internal\Match\Groups\Strategy;

use TRegx\CleanRegex\Internal\InternalPattern;
use TRegx\CleanRegex\Internal\Match\Groups\Descriptor;
use function in_array;

class MatchAllGroupVerifier implements GroupVerifier
{
    /** @var InternalPattern */
    private $pattern;

    public function __construct(InternalPattern $pattern)
    {
        $this->pattern = $pattern;
    }

    public function groupExists($nameOrIndex): bool
    {
        return in_array($nameOrIndex, (new Descriptor($this->pattern))->getGroups(), true);
    }
}
