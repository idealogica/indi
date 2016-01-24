<?php

class BadDefinitionProvider
{
    public function define(Azazaza $a) {}

    public static function defineStatic(Azazaza $a) {}

    public function __invoke(Azazaza $a) {}
}
