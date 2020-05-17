<?php

namespace gendiff\Formatters\pretty;

function normalizeValue($rawValue, $depth)
{
    $iterFormatArray = function ($array, $depth) use (&$iterFormatArray) {
        $indentStrInn = makeIndent($depth + 1);
        $indentStrOuter = makeIndent($depth);

        $formattedArray = array_map(function ($value, $key) use (&$iterFormatArray, $indentStrInn, $depth) {
            if (is_array($value)) {
                return "{$indentStrInn}{$key}: " . $iterFormatArray($value, $depth + 1);
            } else {
                return "{$indentStrInn}{$key}: $value";
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
        throw new \Exception("Indent cannot be less than 0");
    }
    return implode("", array_fill(0, $spacesCount, " "));
}

function renderPrettyDiff($ast)
{
    $iter = function ($ast, $depth = 1) use (&$iter) {
        $diffs = array_map(function ($elem) use (&$iter, $depth) {
            $name = $elem['name'];
            $value = isset($elem['value']) ? normalizeValue($elem['value'], $depth) : null;
            $oldValue = isset($elem['oldValue']) ? normalizeValue($elem['oldValue'], $depth) : null;
            $indentStr = makeIndent($depth, -2);

            switch ($elem['type']) {
                case 'parent':
                    $prefix = '  ';
                    return "{$indentStr}  {$name}: " . $iter($elem['children'], $depth + 1);
                case 'unchanged':
                    $prefix = '  ';
                    $result = "$indentStr  $name: $value";
                    break;
                case 'deleted':
                    $prefix = '- ';
                    $result = "$indentStr- $name: $value";
                    break;
                case 'added':
                    $result = "$indentStr+ $name: $value";
                    break;
                case 'changed':
                    $result = "$indentStr+ $name: $value\n$indentStr- $name: $oldValue";
                    break;
                default:
                    $unknownType = $elem['type'];
                    throw new \Exception("Difference type: '$unknownType' not found!\n");
                    break;
            }

            return $result;
        }, $ast);

        $shortIndentStr = makeIndent($depth - 1);
        return "{\n" . implode("\n", $diffs) . "\n$shortIndentStr}";
    };

    $diff = $iter($ast);
    return $diff;
}
