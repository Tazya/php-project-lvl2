<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson($content)
{
    return json_decode($content, true);
}

function parseYaml($content)
{
    return Yaml::parse($content);
}
