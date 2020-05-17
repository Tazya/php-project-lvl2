<?php

namespace gendiff\Formatters\json;

function renderJsonDiff($ast)
{
    return json_encode($ast, JSON_PRETTY_PRINT);
}
