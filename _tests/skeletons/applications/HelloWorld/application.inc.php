<?php
namespace Lapurd\Application\HelloWorld;

function paths()
{
    return array(
        'index' => array(
            'callback' => 'sayHelloWorld',
        ),
    );
}

function modules()
{
    return array(
        'Foo',
    );
}
