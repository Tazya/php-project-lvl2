<?php

namespace gendiff\differ;

use function gendiff\parsers\parse;
use function gendiff\Formatters\plain\renderPlainDiff;
use function gendiff\Formatters\pretty\renderPrettyDiff;
use function gendiff\Formatters\json\renderJsonDiff;

function makeAst($firstProperties, $secondProperties)
{
    $allKeys = array_unique(
        array_merge(
            array_keys($firstProperties),
            array_keys($secondProperties)
        )
    );

    $ast = array_map(function ($key) use (&$iter, $firstProperties, $secondProperties) {
        if (!hasProperty($key, $firstProperties) && hasProperty($key, $secondProperties)) {
            return ['name' => $key, 'type' => 'added', 'value' => $secondProperties[$key]];
        }
        
        if (hasProperty($key, $firstProperties) && !hasProperty($key, $secondProperties)) {
            return ['name' => $key, 'type' => 'deleted', 'value' => $firstProperties[$key]];
        }
        
        $firstProperty = $firstProperties[$key];
        $secondProperty = $secondProperties[$key];

        if ($firstProperty === $secondProperty) {
            return ['name' => $key, 'type' => 'unchanged', 'value' => $firstProperty];
        }

        if (is_array($firstProperty) && is_array($secondProperty)) {
            return ['name' => $key, 'type' => 'parent', 'children' => makeAst($firstProperty, $secondProperty)];
        }

        if ($firstProperty !== $secondProperty) {
            return [
                'name' => $key,
                'type' => 'changed',
                'value' => $secondProperty,
                'oldValue' => $firstProperty
            ];
        }
    }, $allKeys);

    return array_values($ast);
}

function hasProperty($key, $properties)
{
    return array_key_exists($key, $properties);
}

function getContent($path)
{
    $normalized = str_replace("\\", "/", $path);
    if ($normalized[0] == '/') {
        $currentDirectory = '';
    } else {
        $currentDirectory = getcwd() . "/";
    }
    $fullPath = realpath($currentDirectory . $normalized);

    if (!$fullPath) {
        throw new \Exception("File '$path' not found!");
    }

    $content = file_get_contents($fullPath);

    return $content;
}

function generateDiff(string $firstPath, string $secondPath, $format = "pretty")
{
    $firstFileExt = pathinfo($firstPath, PATHINFO_EXTENSION);
    $secondFileExt = pathinfo($secondPath, PATHINFO_EXTENSION);

    $firstProperties = parse($firstFileExt, getContent($firstPath));
    $secondProperties = parse($secondFileExt, getContent($secondPath));

    $ast = makeAst($firstProperties, $secondProperties);

    switch ($format) {
        case 'pretty':
            $diff = renderPrettyDiff($ast);
            break;
        case 'plain':
            $diff = renderPlainDiff($ast);
            break;
        case 'json':
            $diff = renderJsonDiff($ast);
            break;
                
        default:
            throw new \Exception("[Render error] Format '$format' is unknown for rendering");
    }

    return $diff;
}
