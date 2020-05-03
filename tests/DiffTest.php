<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\findDiff;
use function gendiff\differ\generateDiff;
use function gendiff\parsers\parse;
use function gendiff\differ\makeAst;
use function gendiff\differ\getContent;
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
        $expected1 = ['diff' => 'unchanged', 'value' => 'one', 'oldValue' => ''];
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
        $expected = file_get_contents('tests/fixtures/expected/flatPretty.txt');

        $expectedPlain = file_get_contents('tests/fixtures/expected/flatPlain.txt');
        
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
        $expected = file_get_contents('tests/fixtures/expected/recursivePretty.txt');

        $expectedPlain = file_get_contents('tests/fixtures/expected/recursivePlain.txt');
        
        $diffJson = generateDiff($this->recursiveJsonConfigPath, $this->changedRecursiveJsonConfigPath);
        $plainDiffJson = generateDiff($this->recursiveJsonConfigPath, $this->changedRecursiveJsonConfigPath, "plain");

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expectedPlain, $plainDiffJson);
    }

    public function testMakeAst()
    {
        $firstProperties = parse('json', file_get_contents($this->recursiveJsonConfigPath));
        $secondProperties = parse('yml', file_get_contents($this->changedRecursiveJsonConfigPath));
        $expected = [
            [
                "name" => "common",
                "children" => [
                    [
                        "name" => "setting1",
                        "type" => "unchanged",
                        "value" => "Value 1"
                    ],
                    [
                        "name" => "setting2",
                        "type" => "deleted",
                        "value" => "200"
                    ],
                    [
                        "name" => "setting3",
                        "type" => "unchanged",
                        "value" => true
                    ],
                    [
                        "name" => "setting6",
                        "type" => "deleted",
                        "value" => ["key" => "value"]
                    ],
                    [
                        "name" => "setting4",
                        "type" => "added",
                        "value" => "blah blah"

                    ],
                    [
                        "name" => "setting5",
                        "type" => "added",
                        "value" => ["key5" => "value5"]
                    ]
                ]
            ],
            [
                "name" => "group1",
                "children" => [
                    [
                        "name" => "baz",
                        "type" => "changed",
                        "value" => "bars",
                        "oldValue" => "bas"
                    ],
                    [
                        "name" => "foo",
                        "type" => "unchanged",
                        "value" => "bar"
                    ]
                ]
            ],
            [
                "name" => "group2",
                "type" => "deleted",
                "value" => ["abc" => "12345"]
            ],
            [
                "name" => "group3",
                "type" => "added",
                "value" => ["fee" => "100500"]
            ]
        ];
        
        $ast = makeAst($firstProperties, $secondProperties);
        $this->assertSame($expected, $ast);
    }

    public function testGetContentNotExisted()
    {
        $notExistedPath = "tests/fixtures/notExisted.json";

        $this->expectExceptionMessage("File '$notExistedPath' not found!\n");
        getContent($notExistedPath);
    }
}
