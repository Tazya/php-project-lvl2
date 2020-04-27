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

function findDifferences($firstProperties, $secondProperties)
{
    $firstPropertiesNormalized = array_map(function ($property) {
        return is_bool($property) ? 'true' : $property;
    }, $firstProperties);

    $secondPropertiesNormalized = array_map(function ($property) {
        return is_bool($property) ? 'true' : $property;
    }, $secondProperties);
    
    $allKeys = array_keys(array_merge($firstProperties, $secondProperties));

    $differences = array_reduce(
        $allKeys,
        function ($carry, $key) use ($firstPropertiesNormalized, $secondPropertiesNormalized) {
            $firstProperty = isset($firstPropertiesNormalized[$key]) ? $firstPropertiesNormalized[$key] : null;
            $secondProperty = isset($secondPropertiesNormalized[$key]) ? $secondPropertiesNormalized[$key] : null;
        
            if ($firstProperty === $secondProperty) {
                $carry[] = "$key: $firstProperty";
            } elseif ($firstProperty === null && $secondProperty !== null) {
                $carry[] = "+ $key: $secondProperty";
            } elseif ($firstProperty !== null && $secondProperty === null) {
                $carry[] = "- $key: $firstProperty";
            } else {
                $carry[] = "+ $key: $secondProperty";
                $carry[] = "- $key: $firstProperty";
            }

            return $carry;
        },
        []
    );

    return $differences;
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

    $differences = findDifferences($firstProperties, $secondProperties);

    return array_reduce($differences, function ($carry, $property) {
        return "{$carry}{$property}\n";
    }, '');
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
