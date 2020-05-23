<?php
use Psr\Container\ContainerInterface;

class ValueProvider
{
    /**
     * @param ContainerInterface $c
     * @param string $argument1
     * @param string $argument2
     */
    public function __invoke(
        ContainerInterface $c,
        string $argument1,
        string $argument2
    ) {
        $c->$argument1 = 'test_value';
        $c->$argument2 = 'test_value';
    }
}
