<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function parse($ext, $content)
{
    switch ($ext) {
        case 'json':
            return json_decode($content, true);
        case 'yml':
            return Yaml::parse($content);
                    
        default:
            throw new \Exception("File has an unknown extension: '$ext'");
    }
}
