<?php
use Idealogica\InDI;
use Psr\Container\ContainerInterface;

class DefinitionProvider
{
    /**
     * @param string $arg1
     * @param InDI\Container $c
     * @param string $arg2
     *
     * @return stdClass
     */
    public function define(string $arg1, InDI\Container $c, string $arg2)
    {
        if($arg1 === 'arg1' && $arg2 === 'arg2')
        {
            $obj = new stdClass();
            $obj->var = 'pass';
            return $obj;
        }
    }

    /**
     * @param ContainerInterface $c
     * @param string $arg1
     * @param string $arg2
     *
     * @return stdClass
     */
    public static function defineStatic(ContainerInterface $c, string $arg1, string $arg2)
    {
        if($arg1 === 'arg1' && $arg2 === 'arg2')
        {
            $obj = new stdClass();
            $obj->var = 'pass';
            return $obj;
        }
    }

    /**
     * @param string $arg1
     * @param string $arg2
     * @param InDI\Container $c
     *
     * @return stdClass
     */
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
