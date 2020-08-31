<?php

namespace Eightfold\HtmlSpecStructured\Tests\Read;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use Eightfold\HtmlSpecStructured\Read\HtmlAttributeIndex;

class HtmlAttributeIndexTest extends PHPUnitTestCase
{
    /**
     * @test
     * @group current
     */
    public function generates_element_files_and_folders()
    {
        // Globals
        $expected = ["html-attributes", "abbr.json"];
        $actual = HtmlAttributeIndex::all()->pathPartsFor("abbr");
        $this->assertEquals($expected, $actual);

        // $expected = 154;
        // $actual = HtmlIndex::all()->elements();
        // $actual = count($actual);
        // $this->assertEquals($expected, $actual);

        // $expected = "a";
        // $actual = HtmlIndex::all()->elementNamed("a")->name();
        // $this->assertEquals($expected, $actual);

    }
}
