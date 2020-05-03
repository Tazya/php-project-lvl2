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
        $filteredAst = array_filter($ast, function ($elem) {
            return !(isset($elem['diff']) && $elem['diff'] === "same");
        });

        $diffs = array_map(function ($elem) use (&$iter, $parents) {
            $name = $elem['name'];
            $newParents = array_merge($parents, [$name]);

            if (isset($elem['children'])) {
                return $iter($elem['children'], $newParents);
            }

            $value = normalizeValue($elem['value']);
            $oldValue = isset($elem['oldValue']) ? normalizeValue($elem['oldValue']) : '';

            switch ($elem['diff']) {
                case 'changed':
                    $elemDiff = "changed. From '$oldValue' to '$value'";
                    break;
                case 'deleted':
                    $elemDiff = 'removed';
                    break;
                case 'added':
                    $elemDiff = "added with value: '$value'";
                    break;
            }

            if ($elem['diff'] === 'same') {
                $result = '';
            }

            $parentsStr = implode('.', $newParents);
            $result = "Property '$parentsStr' was $elemDiff";

            return $result;
        }, $filteredAst);

        return implode("\n", $diffs);
    };

    $diff = $iter($ast);
    return $diff;
}
