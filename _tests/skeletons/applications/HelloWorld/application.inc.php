<?php
namespace Lapurd\Application\HelloWorld;

function info()
{
    return array(
        'theme' => 'Bar',
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
