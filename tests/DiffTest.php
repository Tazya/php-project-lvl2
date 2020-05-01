<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\findDiff;
use function gendiff\differ\generateDiff;
use function gendiff\parsers\parseFile;
use function gendiff\differ\makeAst;
use function gendiff\differ\renderDiff;

class DiffTest extends TestCase
{
    protected $jsonConfigPath;
    protected $changedJsonConfigPath;
    protected $yamlConfigPath;
    protected $changedYamlConfigPath;

    protected $recursiveJsonConfigPath;
    protected $changedRecursiveJsonConfigPath;

    protected function setUp(): void
    {
        $this->jsonConfigPath = "tests/fixtures/flatBefore.json";
        $this->changedJsonConfigPath = "tests/fixtures/flatAfter.json";
        $this->yamlConfigPath = "tests/fixtures/flatBefore.yml";
        $this->changedYamlConfigPath = "tests/fixtures/flatAfter.yml";
        $this->recursiveJsonConfigPath = "tests/fixtures/recursiveBefore.json";
        $this->changedRecursiveJsonConfigPath = "tests/fixtures/recursiveAfter.json";
    }

    public function testFindDiff()
    {
        $expected1 = ['diff' => 'same', 'value' => 'one', 'oldValue' => ''];
        $expected2 = ['diff' => 'added', 'value' => 'add', 'oldValue' => ''];
        $expected3 = ['diff' => 'deleted', 'value' => 'delete', 'oldValue' => ''];
        $expected4 = ['diff' => 'changed', 'value' => 2, 'oldValue' => 0];

        $this->assertSame($expected1, findDiff('one', 'one'));
        $this->assertSame($expected2, findDiff(null, 'add'));
        $this->assertSame($expected3, findDiff('delete', null));
        $this->assertSame($expected4, findDiff(0, 2));
    }

    public function testGenerateDiffFlat()
    {
        $expected = "{
    host: hexlet.io
  + timeout: 20
  - timeout: 50
  - proxy: 123.234.53.22
  + verbose: true
}
";

        $expectedPlain = "Property 'timeout' was changed. From '50' to '20'
Property 'proxy' was removed
Property 'verbose' was added with value: 'true'
";
        
        $diffJson = generateDiff($this->jsonConfigPath, $this->changedJsonConfigPath);
        $diffYaml = generateDiff($this->yamlConfigPath, $this->changedYamlConfigPath);

        $plainDiffJson = generateDiff($this->jsonConfigPath, $this->changedJsonConfigPath, "plain");
        $plainDiffYaml = generateDiff($this->yamlConfigPath, $this->changedYamlConfigPath, "plain");

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);

        $this->assertSame($expectedPlain, $plainDiffJson);
        $this->assertSame($expectedPlain, $plainDiffYaml);
    }

    public function testGenerateDiffRecursive()
    {
        $expected = "{
    common: {
        setting1: Value 1
      - setting2: 200
        setting3: true
      - setting6: {
            key: value
        }
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
    }
    group1: {
      + baz: bars
      - baz: bas
        foo: bar
    }
  - group2: {
        abc: 12345
    }
  + group3: {
        fee: 100500
    }
}
";

        $expectedPlain = "Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'group1.baz' was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'
";
        
        $diffJson = generateDiff($this->recursiveJsonConfigPath, $this->changedRecursiveJsonConfigPath);
        $plainDiffJson = generateDiff($this->recursiveJsonConfigPath, $this->changedRecursiveJsonConfigPath, "plain");

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expectedPlain, $plainDiffJson);
    }

    public function testMakeAst()
    {
        $firstProperties = parseFile($this->recursiveJsonConfigPath);
        $secondProperties = parseFile($this->changedRecursiveJsonConfigPath);
        $expected = [
            [
                "name" => "common",
                "children" => [
                    [
                        "name" => "setting1",
                        "diff" => "same",
                        "value" => "Value 1"
                    ],
                    [
                        "name" => "setting2",
                        "diff" => "deleted",
                        "value" => "200"
                    ],
                    [
                        "name" => "setting3",
                        "diff" => "same",
                        "value" => true
                    ],
                    [
                        "name" => "setting6",
                        "diff" => "deleted",
                        "value" => ["key" => "value"]
                    ],
                    [
                        "name" => "setting4",
                        "diff" => "added",
                        "value" => "blah blah"

                    ],
                    [
                        "name" => "setting5",
                        "diff" => "added",
                        "value" => ["key5" => "value5"]
                    ]
                ]
            ],
            [
                "name" => "group1",
                "children" => [
                    [
                        "name" => "baz",
                        "diff" => "changed",
                        "value" => "bars",
                        "oldValue" => "bas"
                    ],
                    [
                        "name" => "foo",
                        "diff" => "same",
                        "value" => "bar"
                    ]
                ]
            ],
            [
                "name" => "group2",
                "diff" => "deleted",
                "value" => ["abc" => "12345"]
            ],
            [
                "name" => "group3",
                "diff" => "added",
                "value" => ["fee" => "100500"]
            ]
        ];
        
        $ast = makeAst($firstProperties, $secondProperties);
        $this->assertSame($expected, $ast);
    }
}
