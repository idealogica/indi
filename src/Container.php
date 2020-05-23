<?php
namespace Idealogica\InDI;

use Psr\Container as PsrContainer;

class Container implements \Iterator, \ArrayAccess, \Countable, PsrContainer\ContainerInterface
{
    use ContainerTrait;
    use ArrayAccessTrait;
    use PropertyAccessTrait;
}
