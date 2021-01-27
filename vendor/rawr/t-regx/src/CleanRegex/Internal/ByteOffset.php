<?php
namespace TRegx\CleanRegex\Internal;

class ByteOffset
{
    public static function toCharacterOffset(string $subject, int $offset): int
    {
        return \mb_strlen(\substr($subject, 0, $offset));
    }
}
