<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\findDiff;
use function gendiff\differ\generateDiff;
use function gendiff\differ\makeAst;
use function gendiff\differ\normalizeValueForRender;
use function gendiff\differ\makeIndent;
use function gendiff\differ\parseFile;
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

    public function testNormalizeValueForRender()
    {
        $expected = "{
    one: {
        two: ex
    }
    three: bex
}";
        $this->assertSame('one', normalizeValueForRender('one', 1));
        $this->assertSame('true', normalizeValueForRender(true, 1));
        $this->assertSame('false', normalizeValueForRender(false, 1));
        $this->assertSame($expected, normalizeValueForRender(['one' => ['two' => 'ex'], 'three' => 'bex'], 0));
    }

    public function testMakeIndent()
    {
        $this->assertSame("", makeIndent(0));
        $this->assertSame("    ", makeIndent(1));
        $this->assertSame("  ", makeIndent(1, -2));

        $this->expectExceptionMessage("Indent cannot be less than 0\n");
        makeIndent(0, -2);
    }

    public function testGenerateDiffFileNotFound()
    {

        $path = "tests/fixtures/notExist.json";

        $this->expectExceptionMessage("File $path not found!\n");
        $diffJson = generateDiff($path, $this->jsonConfigPath);
    }

    public function testGenerateDiff()
    {
        $expected = "{
    host: hexlet.io
  + timeout: 20
  - timeout: 50
  - proxy: 123.234.53.22
  + verbose: true
}
";
        
        $diffJson = generateDiff($this->jsonConfigPath, $this->changedJsonConfigPath);
        $diffYaml = generateDiff($this->yamlConfigPath, $this->changedYamlConfigPath);

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);
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

    public function testRenderDiff()
    {
        $expected = "{
    host: hexlet.io
  + timeout: 20
  - timeout: 50
  - proxy: 123.234.53.22
  + verbose: true
}
";
        
        $expectedRecursive = "{
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

        $firstProperties = parseFile($this->jsonConfigPath);
        $secondProperties = parseFile($this->changedJsonConfigPath);
        $ast = makeAst($firstProperties, $secondProperties);
        $renderedDiff = renderDiff($ast);
        $this->assertSame($expected, $renderedDiff);

        $firstRecursiveProperties = parseFile($this->recursiveJsonConfigPath);
        $secondRecursiveProperties = parseFile($this->changedRecursiveJsonConfigPath);
        $astRecursive = makeAst($firstRecursiveProperties, $secondRecursiveProperties);
        $renderedRecursiveDiff = renderDiff($astRecursive);
        $this->assertSame($expectedRecursive, $renderedRecursiveDiff);
    }
}
