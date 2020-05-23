<?php
namespace Idealogica\InDI\Exception;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
    /**
     * Container constructor.
     *
     * @param string $message
     * @param string ...$insertions
     */
    public function __construct(string $message = "", string ...$insertions)
    {
        parent::__construct(sprintf($message, ...$insertions));
    }
}
