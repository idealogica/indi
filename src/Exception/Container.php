<?php
namespace Idealogica\InDI\Exception;

use Interop\Container\Exception;

class Container extends \Exception implements Exception\ContainerException
{
    /**
     * Container constructor.
     *
     * @param string $message
     * @param string[] ...$insertions
     */
    public function __construct(string $message = "", string ...$insertions)
    {
        parent::__construct(sprintf($message, ...$insertions));
    }
}