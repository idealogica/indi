<?php
use Idealogica\InDI;
use Interop\Container;

class DefinitionProvider
{
    public function define(string $arg1, InDI\Container $c, string $arg2)
    {
        if($arg1 === 'arg1' && $arg2 === 'arg2')
        {
            $obj = new stdClass();
            $obj->var = 'pass';
            return $obj;
        }
    }

    public static function defineStatic(Container\ContainerInterface $c, string $arg1, string $arg2)
    {
        if($arg1 === 'arg1' && $arg2 === 'arg2')
        {
            $obj = new stdClass();
            $obj->var = 'pass';
            return $obj;
        }
    }

    public function __invoke(string $arg1, string $arg2, InDI\Container $c)
    {
        if($arg1 === 'arg1' && $arg2 === 'arg2')
        {
            $obj = new stdClass();
            $obj->var = 'pass';
            return $obj;
        }
    }
}
