<?php
namespace Lapurd\Module;

class Foo extends \Lapurd\Component\Module
{
    public function say($word)
    {
        echo "You are saying: '$word'!";
    }
}
