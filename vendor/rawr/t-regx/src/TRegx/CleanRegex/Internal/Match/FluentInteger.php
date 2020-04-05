<?php
namespace TRegx\CleanRegex\Internal\Match;

use TRegx\CleanRegex\Exception\FluentMatchPatternException;
use TRegx\CleanRegex\Exception\IntegerFormatException;
use TRegx\CleanRegex\Internal\Integer;
use TRegx\CleanRegex\Match\Details\Group\MatchGroup;
use TRegx\CleanRegex\Match\Details\Match;

class FluentInteger
{
    public static function parse($value)
    {
        if (\is_int($value)) {
            return $value;
        }
        if ($value instanceof Match || $value instanceof MatchGroup) {
            return $value->toInt();
        }
        if (!\is_string($value)) {
            throw FluentMatchPatternException::forInvalidInteger($value);
        }
        if (Integer::isValid($value)) {
            return (int)$value;
        }
        throw IntegerFormatException::forFluent($value);
    }
}
