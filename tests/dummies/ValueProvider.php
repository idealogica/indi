<?php
use Interop\Container;

class ValueProvider
{
    public function __invoke(
        Container\ContainerInterface $c,
        string $argument1,
        string $argument2)
    {
        $c->$argument1 = 'test_value';
        $c->$argument2 = 'test_value';
    }
}
