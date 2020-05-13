<?php

namespace gendiff\cli;

use function gendiff\differ\generateDiff;

const DOC = "
Generate Differences

Usage:
  gendiff (-h | --help)
  gendiff (-v | --version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help       Show this screen
  -v --version    Show version
  --format <fmt>  Report format. Available: pretty, plain, json [default: pretty]
";

function run()
{
    $params = [
        'help' => true,
        'version' => 'Gendiff v0.0.1'
    ];

    $args = \Docopt::handle(DOC, $params);

    $firstFilepath = $args['<firstFile>'];
    $secondFilepath = $args['<secondFile>'];
    $format = $args['--format'];

    $diff = generateDiff($firstFilepath, $secondFilepath, $format);

    print_r($diff . "\n");
}
