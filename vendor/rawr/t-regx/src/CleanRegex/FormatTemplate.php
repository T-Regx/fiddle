<?php
namespace TRegx\CleanRegex;

use TRegx\CleanRegex\Exception\TemplateFormatException;
use TRegx\CleanRegex\Internal\Format\LiteralTokenValue;
use TRegx\CleanRegex\Internal\Format\TokenValue;
use TRegx\CleanRegex\Internal\Prepared\Format\FormatTokenValue;
use TRegx\CleanRegex\Internal\Prepared\Format\TemplateStrategy;
use TRegx\CleanRegex\Internal\Prepared\Parser\BindingParser;
use TRegx\CleanRegex\Internal\Prepared\Parser\InjectParser;
use TRegx\CleanRegex\Internal\Prepared\Parser\TemplateParser;
use TRegx\CleanRegex\Internal\Prepared\Prepare;

class FormatTemplate
{
    /** @var string */
    private $pattern;
    /** @var bool */
    private $pcre;
    /** @var string */
    private $flags;
    /** @var TokenValue[] */
    private $placeholders;

    public function __construct(string $pattern, string $flags, bool $pcre, array $placeholders)
    {
        $this->pattern = $pattern;
        $this->pcre = $pcre;
        $this->flags = $flags;
        $this->placeholders = $placeholders;
    }

    public function formatting(string $formatString, array $tokens): FormatTemplate
    {
        return $this->next(new FormatTokenValue($formatString, $tokens));
    }

    public function literal(): FormatTemplate
    {
        return $this->next(new LiteralTokenValue());
    }

    private function next(TokenValue $placeholder): FormatTemplate
    {
        return new FormatTemplate($this->pattern, $this->flags, $this->pcre, \array_merge($this->placeholders, [$placeholder]));
    }

    public function build(): PatternInterface
    {
        $this->validateTokensAndMethods();
        return Prepare::build(new TemplateParser($this->pattern, $this->placeholders), $this->pcre, $this->flags);
    }

    public function inject(array $values): PatternInterface
    {
        $this->validateTokensAndMethods();
        return Prepare::build(new InjectParser($this->pattern, $values, new TemplateStrategy($this->placeholders)), $this->pcre, $this->flags);
    }

    public function bind(array $values): PatternInterface
    {
        $this->validateTokensAndMethods();
        return Prepare::build(new BindingParser($this->pattern, $values, new TemplateStrategy($this->placeholders)), $this->pcre, $this->flags);
    }

    private function validateTokensAndMethods(): void
    {
        $tokens = \preg_match_all('/&/', $this->pattern);
        $count = \count($this->placeholders);
        if ($tokens < $count) {
            throw TemplateFormatException::insufficient($tokens, $count);
        }
        if ($tokens > $count) {
            throw TemplateFormatException::superfluous($tokens, $count);
        }
    }
}
