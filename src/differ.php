<?php

namespace gendiff\differ;

use function gendiff\parsers\parse;
use function gendiff\Formatters\plain\renderPlainDiff;
use function gendiff\Formatters\pretty\renderPrettyDiff;
use function gendiff\Formatters\json\renderJsonDiff;

function makeAst($firstProperties, $secondProperties)
{
    $iter = function ($firstProperties, $secondProperties) use (&$iter) {
        $allKeys = array_keys(array_merge($firstProperties, $secondProperties));

        if (empty($allKeys)) {
            return [];
        }

        $ast = array_map(function ($key) use (&$iter, $firstProperties, $secondProperties) {
            $firstProperty = isset($firstProperties[$key]) ? $firstProperties[$key] : null;
            $secondProperty = isset($secondProperties[$key]) ? $secondProperties[$key] : null;

            if ($firstProperty === null && $secondProperty !== null) {
                return ['name' => $key, 'type' => 'added', 'value' => $secondProperty];
            }
            
            if ($firstProperty !== null && $secondProperty === null) {
                return ['name' => $key, 'type' => 'deleted', 'value' => $firstProperty];
            }
            
            if ($firstProperty === $secondProperty) {
                return ['name' => $key, 'type' => 'unchanged', 'value' => $firstProperty];
            }

            if (is_array($firstProperty) && is_array($secondProperty)) {
                return ['name' => $key, 'children' => $iter($firstProperty, $secondProperty)];
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

        return $ast;
    };

    return $iter($firstProperties, $secondProperties);
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
            throw new \Exception("Unknown format '$format'");
    }

    return $diff;
}
