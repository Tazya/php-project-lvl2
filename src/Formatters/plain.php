<?php

namespace gendiff\Formatters\plain;

function normalizeValue($rawValue)
{
    if (is_bool($rawValue)) {
        $normalizedValue = $rawValue ? 'true' : 'false';
    } elseif (is_array($rawValue)) {
        $normalizedValue = 'complex value';
    } else {
        $normalizedValue = $rawValue;
    }

    return $normalizedValue;
}

function renderPlainDiff($ast)
{
    $iter = function ($ast, $parents = []) use (&$iter) {
        $filteredAst = array_filter($ast, fn($elem) => $elem['type'] !== "unchanged");

        $diffs = array_map(function ($elem) use (&$iter, $parents) {
            $name = $elem['name'];
            $newParents = array_merge($parents, [$name]);
            $parentsStr = implode('.', $newParents);

            switch ($elem['type']) {
                case 'parent':
                    return $iter($elem['children'], $newParents);
                case 'changed':
                    $newValue = normalizeValue($elem['newValue']);
                    $oldValue = normalizeValue($elem['oldValue']);
                    return "Property '$parentsStr' was changed. From '$oldValue' to '$newValue'";
                case 'deleted':
                    return "Property '$parentsStr' was removed";
                case 'added':
                    $value = normalizeValue($elem['value']);
                    return "Property '$parentsStr' was added with value: '$value'";
                default:
                    $unknownType = $elem['type'];
                    throw new \Exception("Difference type: '$unknownType' not found!\n");
            }
        }, $filteredAst);

        return implode("\n", $diffs);
    };

    $diff = $iter($ast);
    return $diff;
}
