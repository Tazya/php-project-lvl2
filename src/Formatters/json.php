<?php

namespace gendiff\Formatters\json;

function renderJsonDiff($ast)
{
    $iter = function ($ast) use (&$iter) {

        $diffs = array_reduce($ast, function ($carry, $elem) use (&$iter) {
            $name = $elem['name'];

            if (isset($elem['children'])) {
                $children = $iter($elem['children']);
                $result = [
                    $name => [
                       "children" => $children
                    ]
                ];

                $newCarry = array_merge($carry, $result);
            } else {
                $resultDiff = [
                    "difference" => $elem['type'],
                    "value" => $elem['value']
                ];

                $result = isset($elem['oldValue'])
                    ? array_merge($resultDiff, ["oldValue" => $elem['oldValue']])
                    : $resultDiff;

                $newCarry = array_merge($carry, [$name => $result]);
            }

            return $newCarry;
        }, []);

        return $diffs;
    };

    return json_encode($iter($ast), JSON_PRETTY_PRINT);
}
