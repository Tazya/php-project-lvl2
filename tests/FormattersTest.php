<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\Formatters\plain\normalizeValue as normalizeValuePlain;
use function gendiff\Formatters\pretty\normalizeValue as normalizeValuePretty;
use function gendiff\Formatters\pretty\makeIndent;
use function gendiff\Formatters\pretty\renderPrettyDiff;
use function gendiff\Formatters\plain\renderPlainDiff;
use function gendiff\Formatters\json\renderJsonDiff;

class FormattersTest extends TestCase
{
    protected $ast;
    protected $prettyAst;

    protected function setUp(): void
    {
        $this->ast = [
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

        $this->flatAst = [
            [
                "name" => "host",
                "type" => "unchanged",
                "value" => "hexlet.io"
            ],
            [
                "name" => "timeout",
                "type" => "changed",
                "value" => "20",
                "oldValue" => "50"
            ],
            [
                "name" => "proxy",
                "type" => "deleted",
                "value" => "123.234.53.22"
            ],
            [
                "name" => "verbose",
                "type" => "added",
                "value" => true
            ]
        ];
    }

    public function testNormalizeValuePretty()
    {
        $expected = file_get_contents('tests/fixtures/expected/normalized.txt');

        $this->assertSame('one', normalizeValuePretty('one', 1));
        $this->assertSame('true', normalizeValuePretty(true, 1));
        $this->assertSame('false', normalizeValuePretty(false, 1));
        $this->assertSame($expected, normalizeValuePretty(['one' => ['two' => 'ex'], 'three' => 'bex'], 0));
    }

    public function testNormalizeValuePlain()
    {
        $this->assertSame('one', normalizeValuePlain('one'));
        $this->assertSame('true', normalizeValuePlain(true));
        $this->assertSame('false', normalizeValuePlain(false));
        $this->assertSame('complex value', normalizeValuePlain(["key" => "value"]));
    }

    public function testMakeIndent()
    {
        $this->assertSame("", makeIndent(0));
        $this->assertSame("    ", makeIndent(1));
        $this->assertSame("  ", makeIndent(1, -2));

        $this->expectExceptionMessage("Indent cannot be less than 0\n");
        makeIndent(0, -2);
    }

    public function testRenderDiffPretty()
    {
        $expected = file_get_contents('tests/fixtures/expected/recursivePretty.txt');

        $renderedDiff = renderPrettyDiff($this->ast);
        $this->assertSame($expected, $renderedDiff);
    }

    public function testRenderDiffPlain()
    {
        $expected = file_get_contents('tests/fixtures/expected/recursivePlain.txt');

        $renderedDiff = renderPlainDiff($this->ast);
        $this->assertSame($expected, $renderedDiff);
    }

    public function testRenderDiffJson()
    {
        $expected = file_get_contents('tests/fixtures/expected/flatJson.json');
        $expectedRecursive = file_get_contents('tests/fixtures/expected/recursiveJson.json');
        $renderedDiff = renderJsonDiff($this->flatAst);
        $renderedRecursiveDiff = renderJsonDiff($this->ast);

        $this->assertSame($expected, $renderedDiff);
        $this->assertSame($expectedRecursive, $renderedRecursiveDiff);
    }
}
