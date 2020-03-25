<?php

namespace gendiff\cli;

const DOC = "
    Generate Differences

    Usage:
        gendiff
        gendiff -h | --help

    Options:
        -h --help    Show this screen.
    ";

function run()
{
    $params = [
        'help' => true,
        'version' => 'Gendiff v0.0.1'
    ];

    $args = \Docopt::handle(DOC, $params);
}
