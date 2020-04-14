<?php

namespace gendiff\differ;

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
        return "File $firstFile cannot be found!\n";
    }
    if (!$secondPath) {
        return "File $secondFile cannot be found!\n";
    }

    $firstProperties = json_decode(file_get_contents($firstPath), true);
    $secondProperties = json_decode(file_get_contents($secondPath), true);
    $differences = findDifferences($firstProperties, $secondProperties);

    return array_reduce($differences, function ($carry, $property) {
        return "{$carry}{$property}\n";
    }, '');
}
