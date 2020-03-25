<?php

namespace gendiff\cli;

const DOC = "
Generate Differences

Usage:
  gendiff (-h | --help)
  gendiff (-v | --version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help       Show this screen
  -v --version    Show version
  --format <fmt>  Report format [default: pretty]
";

function run()
{
    $params = [
        'help' => true,
        'version' => 'Gendiff v0.0.1'
    ];

    $args = \Docopt::handle(DOC, $params);
}
