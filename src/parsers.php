<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function parse($ext, $content)
{
    if ($ext === 'json') {
        return json_decode($content, true);
    } elseif ($ext === 'yml') {
        return Yaml::parse($content);
    } else {
        throw new \Exception("File has an unknown extension: '$ext'\n");
    }
}
