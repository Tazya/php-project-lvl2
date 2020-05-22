<?php

namespace gendiff\Formatters\pretty;

function normalizeValue($rawValue, $depth)
{
    $iterFormatArray = function ($array, $depth) use (&$iterFormatArray) {
        $indentStrInn = makeIndent($depth + 1);
        $indentStrOuter = makeIndent($depth);

        $formattedValues = array_map(function ($value, $key) use (&$iterFormatArray, $indentStrInn, $depth) {
            if (is_array($value)) {
                return "{$indentStrInn}{$key}: " . $iterFormatArray($value, $depth + 1);
            } else {
                return "{$indentStrInn}{$key}: $value";
            }
        }, $array, array_keys($array));

        $formattedStr = implode("\n", $formattedValues);
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
    return implode("", array_fill(0, $spacesCount, " "));
}

function renderPrettyDiff($ast)
{
    $iter = function ($ast, $depth = 1) use (&$iter) {
        $diffs = array_map(function ($elem) use (&$iter, $depth) {
            $name = $elem['name'];
            $indentStr = makeIndent($depth, -2);

            switch ($elem['type']) {
                case 'parent':
                    return "{$indentStr}  {$name}: " . $iter($elem['children'], $depth + 1);
                case 'unchanged':
                    $value = normalizeValue($elem['value'], $depth);
                    $result = "$indentStr  $name: $value";
                    break;
                case 'deleted':
                    $value = normalizeValue($elem['value'], $depth);
                    $result = "$indentStr- $name: $value";
                    break;
                case 'added':
                    $value = normalizeValue($elem['value'], $depth);
                    $result = "$indentStr+ $name: $value";
                    break;
                case 'changed':
                    $newValue = normalizeValue($elem['newValue'], $depth);
                    $oldValue = normalizeValue($elem['oldValue'], $depth);
                    $result = "$indentStr+ $name: $newValue\n$indentStr- $name: $oldValue";
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
