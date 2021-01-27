<?php
namespace TRegx\CleanRegex\Internal\Prepared\Quotable;

use InvalidArgumentException;
use TRegx\CleanRegex\Internal\Prepared\Quotable\Factory\Alternator;
use TRegx\CleanRegex\Internal\Type;

class AlternationQuotable implements Quotable
{
    /** @var array */
    private $userInputs;
    /** @var callable|null */
    private $duplicateMapper;

    public function __construct(array $userInputs, ?callable $duplicateMapper)
    {
        $this->userInputs = $userInputs;
        $this->duplicateMapper = $duplicateMapper;
    }

    public function quote(string $delimiter): string
    {
        return Alternator::quote($this->normalizedUserInput(), $delimiter);
    }

    private function normalizedUserInput(): array
    {
        foreach ($this->userInputs as $input) {
            $this->validateQuotable($input);
        }
        return $this->removeDuplicates($this->userInputEmptyLast());
    }

    private function validateQuotable($quoteable): void
    {
        if (!\is_string($quoteable)) {
            $type = Type::asString($quoteable);
            throw new InvalidArgumentException("Invalid bound alternate value. Expected string, but $type given");
        }
    }

    private function userInputEmptyLast(): array
    {
        // removes empty strings, and if there was any, appends it to the end
        if (!\in_array('', $this->userInputs)) {
            return $this->userInputs;
        }
        $result = \array_filter($this->userInputs);
        $result[] = '';
        return $result;
    }

    private function removeDuplicates(array $values): array
    {
        if ($this->duplicateMapper) {
            return \array_intersect_key($values, \array_unique(\array_map($this->duplicateMapper, $values)));
        }
        return \array_unique($values);
    }
}
