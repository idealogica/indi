<?php
namespace Idealogica\InDI;

use Interop\Container as InteropContainer;

class Container implements \Iterator, \ArrayAccess, \Countable, InteropContainer\ContainerInterface
{
    use ContainerTrait;
    use ArrayAccessTrait;
    use PropertyAccessTrait;
}
