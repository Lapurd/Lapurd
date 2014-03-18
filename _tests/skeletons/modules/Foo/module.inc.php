<?php
namespace Lapurd\Module\Foo;

function paths()
{
    return array(
        'foo' => array(
            'callback' => 'say',
            'arguments' => array('Foo')
        ),
        'foo/%' => array(
            'callback' => 'say',
            'arguments' => array(1),
        ),
        'foo/%' => array(
            'callback' => 'say',
            'arguments' => array(1),
        ),
    );
}
