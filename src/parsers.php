<?php

namespace gendiff\parsers;

use Symfony\Component\Yaml\Yaml;

function normalizePath($fileName)
{
    $normalized = str_replace('\\', '/', $fileName);
    if ($normalized[0] == '/') {
        $currentDirectory = '';
    } else {
        $currentDirectory = getcwd() . "/";
    }
    return realpath($currentDirectory . $fileName);
}

function parseFile($path)
{
    $normalizedPath = normalizePath($path);

    if (!$normalizedPath) {
        throw new \Exception("File '$path' not found!\n");
    }

    $ext = pathinfo($normalizedPath, PATHINFO_EXTENSION);
    $content = file_get_contents($normalizedPath);

    if ($ext === 'json') {
        return json_decode($content, true);
    } elseif ($ext === 'yml') {
        return Yaml::parse($content);
    } else {
        throw new \Exception("File '$path' has a unknown extension: '$ext'\n");
    }
}
