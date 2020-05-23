<?php

class BadDefinitionProvider
{
    /**
     * @param Azazaza $a
     */
    public function define(Azazaza $a) {}

    /**
     * @param Azazaza $a
     */
    public static function defineStatic(Azazaza $a) {}

    /**
     * @param Azazaza $a
     */
    public function __invoke(Azazaza $a) {}
}
