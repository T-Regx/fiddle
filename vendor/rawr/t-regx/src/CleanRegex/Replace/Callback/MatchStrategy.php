<?php
namespace TRegx\CleanRegex\Replace\Callback;

use TRegx\CleanRegex\Match\Details\ReplaceDetail;

class MatchStrategy implements ReplaceCallbackArgumentStrategy
{
    public function mapArgument(ReplaceDetail $detail): ReplaceDetail
    {
        return $detail;
    }
}
