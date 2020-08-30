<?php

namespace Eightfold\HtmlSpecStructured\Tests\Read;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use Eightfold\HtmlSpecStructured\Read\HtmlIndex;

class HtmlIndexTest extends PHPUnitTestCase
{
    /**
     * @test
     * @group current
     */
    public function generates_element_files_and_folders()
    {
        $expected = ["html", "textlevel-semantics", "a.json"];
        $actual = HtmlIndex::all()->pathPartsFor("a");
        $this->assertEquals($expected, $actual);

        $expected = 154;
        $actual = HtmlIndex::all()->elements();
        $actual = count($actual);
        $this->assertEquals($expected, $actual);

        $expected = "a";
        $actual = HtmlIndex::all()->elementNamed("a")->name();
        $this->assertEquals($expected, $actual);

    }
}
