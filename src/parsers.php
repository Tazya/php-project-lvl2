<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function parse($format, $content)
{
    switch ($format) {
        case 'json':
            return json_decode($content, true);
        case 'yml':
            return Yaml::parse($content);
        default:
            throw new \Exception("[Parse error] Format '$format' is unknown for parsing");
    }
}
