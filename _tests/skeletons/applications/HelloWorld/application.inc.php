<?php
namespace Lapurd\Application\HelloWorld;

function info()
{
    return array(
        'modules' => array(
                'Foo',
        ),
    );
}

function paths()
{
    return array(
        'index' => array(
            'callback' => 'sayHelloWorld',
        ),
    );
}
