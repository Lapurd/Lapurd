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

function init()
{
    $lapurd = \Lapurd\Core::get();
    $lapurd->getTheme()->getRegion('left')->addBlock('about-foo');
    $lapurd->getTheme()->getRegion('right')->addBlock('about-foo-arg');
}

function paths()
{
    return array(
        'index' => array(
            'callback' => 'sayHelloWorld',
        ),
    );
}
