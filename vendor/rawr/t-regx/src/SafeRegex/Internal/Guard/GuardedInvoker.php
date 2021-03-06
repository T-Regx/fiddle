<?php
namespace TRegx\SafeRegex\Internal\Guard;

use TRegx\SafeRegex\Internal\Errors\ErrorsCleaner;
use TRegx\SafeRegex\Internal\ExceptionFactory;
use TRegx\SafeRegex\Internal\Guard\Strategy\DefaultSuspectedReturnStrategy;
use TRegx\SafeRegex\Internal\Guard\Strategy\SuspectedReturnStrategy;
use function call_user_func;

class GuardedInvoker
{
    /** @var callable */
    private $callback;
    /** @var string */
    private $methodName;
    /** @var ErrorsCleaner */
    private $errorsCleaner;
    /** @var ExceptionFactory */
    private $exceptionFactory;

    public function __construct(string $methodName, $pattern, callable $callback, SuspectedReturnStrategy $strategy = null)
    {
        $this->callback = $callback;
        $this->methodName = $methodName;
        $this->errorsCleaner = new ErrorsCleaner();
        $this->exceptionFactory = new ExceptionFactory($pattern, $strategy ?? new DefaultSuspectedReturnStrategy(), $this->errorsCleaner);
    }

    public function catch(): array
    {
        $this->errorsCleaner->clear();
        $result = call_user_func($this->callback);
        $exception = $this->exceptionFactory->retrieveGlobals($this->methodName, $result);
        $this->errorsCleaner->clear();

        return [$result, $exception];
    }
}
