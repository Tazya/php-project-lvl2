<?php

namespace gendiff\Formatters\pretty;

function normalizeValue($rawValue, $depth)
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

function renderPrettyDiff($ast)
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
            $value = normalizeValue($elem['value'], $depth);

            $indentStr2 = makeIndent($depth, -2);
            if ($elem['diff'] === 'changed') {
                $oldValue = normalizeValue($elem['oldValue'], $depth);
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
    return $diff;
}
