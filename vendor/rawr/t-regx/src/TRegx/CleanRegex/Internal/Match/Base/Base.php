<?php
namespace TRegx\CleanRegex\Internal\Match\Base;

use TRegx\CleanRegex\Internal\InternalPattern;
use TRegx\CleanRegex\Internal\Match\UserData;
use TRegx\CleanRegex\Internal\Model\Match\RawMatch;
use TRegx\CleanRegex\Internal\Model\Match\RawMatchOffset;
use TRegx\CleanRegex\Internal\Model\Matches\RawMatches;
use TRegx\CleanRegex\Internal\Model\Matches\RawMatchesOffset;
use TRegx\CleanRegex\Internal\Subjectable;

interface Base extends Subjectable
{
    public function getPattern(): InternalPattern;

    public function match(): RawMatch;

    public function matchOffset(): RawMatchOffset;

    public function matchAll(): RawMatches;

    public function matchAllOffsets(): RawMatchesOffset;

    public function getUserData(): UserData;
}
