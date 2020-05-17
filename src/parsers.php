<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function parse($format, $content)
{
    switch ($format) {
        case 'json':
            $result = json_decode($content, true);
            break;
        case 'yml':
            $result = Yaml::parse($content);
            break;

        default:
            throw new \Exception("[Parse error] Format '$format' is unknown for parsing");
    }

    if (!$result) {
        throw new \Exception("[Parse error] The '$format' is not valid or could not be read");
    }

    return $result;
}
