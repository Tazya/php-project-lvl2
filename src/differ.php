<?php

namespace gendiff\differ;

use function gendiff\parsers\parse;
use function gendiff\Formatters\plain\renderPlainDiff;
use function gendiff\Formatters\pretty\renderPrettyDiff;
use function gendiff\Formatters\json\renderJsonDiff;

function findDiff($firstProperty, $secondProperty)
{
    if ($firstProperty === $secondProperty) {
        $difference = 'unchanged';
        $value = $firstProperty;
    } elseif ($firstProperty === null && $secondProperty !== null) {
        $difference = 'added';
        $value = $secondProperty;
    } elseif ($firstProperty !== null && $secondProperty === null) {
        $difference = 'deleted';
        $value = $firstProperty;
    } else {
        $difference = 'changed';
        $value = $secondProperty;
        $oldValue = $firstProperty;
    }

    return ['diff' => $difference, 'value' => $value, 'oldValue' => $oldValue ?? ''];
}

function makeAst($firstProperties, $secondProperties)
{
    $iter = function ($firstProperties, $secondProperties, $key = 'main') use (&$iter) {
        $result["name"] = $key;

        if (!is_array($firstProperties) || !is_array($secondProperties)) {
            $diffParams = findDiff($firstProperties, $secondProperties);
            
            $result["type"] = $diffParams["diff"];
            $result["value"] = $diffParams["value"];
            
            if ($diffParams["oldValue"] !== '') {
                $result["oldValue"] = $diffParams["oldValue"];
            }
        } else {
            $allKeys = array_keys(array_merge($firstProperties, $secondProperties));

            $children = array_map(function ($key) use (&$iter, $firstProperties, $secondProperties) {
                $firstProperty = isset($firstProperties[$key]) ? $firstProperties[$key] : null;
                $secondProperty = isset($secondProperties[$key]) ? $secondProperties[$key] : null;
                return $iter($firstProperty, $secondProperty, $key);
            }, $allKeys);
            $result["children"] = $children;
        }

        return $result;
    };

    $ast = $iter($firstProperties, $secondProperties);
    return $ast['children'];
}

function getContent($path)
{
    $normalized = str_replace('\\', '/', $path);
    if ($normalized[0] == '/') {
        $currentDirectory = '';
    } else {
        $currentDirectory = getcwd() . "/";
    }
    $fullPath = realpath($currentDirectory . $path);

    if (!$fullPath) {
        throw new \Exception("File '$path' not found!\n");
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

    if ($format === "plain") {
        $diff = renderPlainDiff($ast);
    } elseif ($format === "json") {
        $diff = renderJsonDiff($ast);
    } else {
        $diff = renderPrettyDiff($ast);
    }

    return $diff;
}
