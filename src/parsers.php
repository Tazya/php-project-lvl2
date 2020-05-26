<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function parse($format, $content)
{
    switch ($format) {
        case 'json':
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        case 'yml':
            return Yaml::parse($content);
        default:
            throw new \Exception("[Parse error] Format '$format' is unknown for parsing");
    }
}
