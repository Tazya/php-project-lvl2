<?php

namespace gendiff\differ;

use function gendiff\parsers\parseFile;
use function gendiff\Formatters\plain\renderPlainDiff;
use function gendiff\Formatters\pretty\renderPrettyDiff;

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

function generateDiff(string $firstPath, string $secondPath, $format = "explain")
{
    $firstProperties = parseFile($firstPath);
    $secondProperties = parseFile($secondPath);

    $ast = makeAst($firstProperties, $secondProperties);

    if ($format === "plain") {
        $diff = renderPlainDiff($ast);
    } else {
        $diff = renderPrettyDiff($ast);
    }

    return $diff;
}
