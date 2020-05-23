<?php
/**
 * @param string $arg1
 * @param string $arg2
 *
 * @return stdClass
 */
function defineValue(string $arg1, string $arg2)
{
    if($arg1 === 'arg1' && $arg2 === 'arg2')
    {
        $obj = new stdClass();
        $obj->var = 'pass';
        return $obj;
    }
}

/**
 * @param Azazaza $a
 */
function badDefineValue(Azazaza $a) {}
