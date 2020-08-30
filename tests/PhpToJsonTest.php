<?php

namespace Eightfold\HtmlSpecStructured\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use Eightfold\HtmlSpecStructured\PhpToJson;

class PhpToJsonTest extends PHPUnitTestCase
{
    // TODO: Make this a CLI utility or something
    /**
     * @test
     * @group generate
     */
    public function generates_element_files_and_folders()
    {
        PhpToJson::compile();

        $jsonDir = PhpToJson::pathPartsToJson();
        $jsonDir[] = "html";
        $jsonDir[] = "textlevel-semantics";
        $jsonDir[] = "a.json";

        $elementPath = implode("/", $jsonDir);

        $actual = file_exists($elementPath);
        $this->assertTrue($actual);
    }
}
