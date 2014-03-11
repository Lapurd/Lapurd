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

function blocks()
{
    return array(
        'about-foo' => array(
            'content' => array(
                'callback' => 'about',
            ),
        ),
        'about-foo-arg' => array(
            'content' => array(
                'callback' => 'about',
                'arguments' => array(1),
            ),
        ),
    );
}
