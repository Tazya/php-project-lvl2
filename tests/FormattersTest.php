<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\Formatters\plain\normalizeValue as normalizeValuePlain;
use function gendiff\Formatters\pretty\normalizeValue as normalizeValuePretty;
use function gendiff\Formatters\pretty\makeIndent;
use function gendiff\Formatters\pretty\renderPrettyDiff;
use function gendiff\Formatters\plain\renderPlainDiff;

class FormattersTest extends TestCase
{
    protected $ast;

    protected function setUp(): void
    {
        $this->ast = [
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
    }

    public function testNormalizeValuePretty()
    {
        $expected = "{
    one: {
        two: ex
    }
    three: bex
}";
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

        $renderedDiff = renderPrettyDiff($this->ast);
        $this->assertSame($expected, $renderedDiff);
    }

    public function testRenderDiffPlain()
    {
        $expected = "Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'group1.baz' was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'
";

        $renderedDiff = renderPlainDiff($this->ast);
        $this->assertSame($expected, $renderedDiff);
    }
}
