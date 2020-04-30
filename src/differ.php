<?php

namespace gendiff\differ;

use function gendiff\parsers\parseJson;
use function gendiff\parsers\parseYaml;

function getPath($fileName)
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
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    if ($ext === 'json') {
        return parseJson(file_get_contents($path));
    } elseif ($ext === 'yml') {
        return parseYaml(file_get_contents($path));
    } else {
        throw new \Exception("File $path has a unknown extension: $ext");
    }
}

function findDiff($firstProperty, $secondProperty)
{
    if ($firstProperty === $secondProperty) {
        $difference = 'same';
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

    $result = ['diff' => $difference, 'value' => $value, 'oldValue' => $oldValue ?? ''];
    return $result;
}

function makeAst($firstProperties, $secondProperties)
{
    $iter = function ($firstProperties, $secondProperties, $key = 'main') use (&$iter) {
        $result["name"] = $key;

        if (!is_array($firstProperties) || !is_array($secondProperties)) {
            $diffParams = findDiff($firstProperties, $secondProperties);
             
            $result["diff"] = $diffParams["diff"];
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

function normalizeValueForRender($rawValue, $depth)
{
    $iterFormatArray = function ($array, $depth) use (&$iterFormatArray) {
        $indentStrInn = makeIndent($depth + 1);
        $indentStrOuter = makeIndent($depth);
        $formattedArray = array_map(function ($value, $key) use (&$iterFormatArray, $indentStrInn, $depth) {
            if (is_array($value)) {
                return "$indentStrInn$key: " . $iterFormatArray($value, $depth + 1);
            } else {
                return "$indentStrInn$key: $value";
            }
        }, $array, array_keys($array));

        $formattedStr = implode("\n", $formattedArray);
        return "{\n$formattedStr\n$indentStrOuter}";
    };


    if (is_bool($rawValue)) {
        $normalizedValue = $rawValue ? 'true' : 'false';
    } elseif (is_array($rawValue)) {
        $normalizedValue = $iterFormatArray($rawValue, $depth);
    } else {
        $normalizedValue = $rawValue;
    }

    return $normalizedValue;
}

function makeIndent($depth, $offset = 0)
{
    $indent = 4;
    $spacesCount = $indent * $depth + $offset;
    if ($spacesCount < 0) {
        throw new \Exception("Indent cannot be less than 0\n");
    }
    return implode("", array_fill(0, $spacesCount, " "));
}

function renderDiff($ast)
{
    $iter = function ($ast, $depth = 1) use (&$iter) {
        $diffs = array_map(function ($elem) use (&$iter, $depth) {
            $indentStr = makeIndent($depth);
            if (isset($elem['children'])) {
                return $indentStr . $elem['name'] . ": " . $iter($elem['children'], $depth + 1);
            }

            switch ($elem['diff']) {
                case 'same':
                    $prefix = '  ';
                    break;
                case 'deleted':
                    $prefix = '- ';
                    break;
                case 'added':
                    $prefix = '+ ';
                    break;
                default:
                    # nothing
                    break;
            }

            $name = $elem['name'];
            $value = normalizeValueForRender($elem['value'], $depth);

            $indentStr2 = makeIndent($depth, -2);
            if ($elem['diff'] === 'changed') {
                $oldValue = normalizeValueForRender($elem['oldValue'], $depth);
                $result = "$indentStr2+ $name: $value\n$indentStr2- $name: $oldValue";
            } else {
                $result = "$indentStr2$prefix$name: $value";
            }

            return $result;
        }, $ast);

        $shortIndentStr = makeIndent($depth - 1);
        return "{\n" . implode("\n", $diffs) . "\n$shortIndentStr}";
    };

    $diff = $iter($ast);
    return $diff . "\n";
}

function generateDiff(string $firstFile, string $secondFile)
{
    $firstPath = getPath($firstFile);
    $secondPath = getPath($secondFile);

    if (!$firstPath) {
        throw new \Exception("File $firstFile not found!\n");
    }
    if (!$secondPath) {
        throw new \Exception("File $secondFile not found!\n");
    }

    $firstProperties = parseFile($firstPath);
    $secondProperties = parseFile($secondPath);

    $ast = makeAst($firstProperties, $secondProperties);
    $diff = renderDiff($ast);

    return $diff;
}
