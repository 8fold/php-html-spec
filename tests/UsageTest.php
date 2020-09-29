<?php

namespace Eightfold\HtmlSpec\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlIndex;
use Eightfold\HtmlSpec\Read\HtmlElement;

use Eightfold\HtmlSpec\Read\HtmlRolesIndex;
use Eightfold\HtmlSpec\Read\HtmlRole;

use Eightfold\HtmlSpec\Read\HtmlContentCategoryIndex;
use Eightfold\HtmlSpec\Read\HtmlContentCategory;

use Eightfold\HtmlSpec\Read\HtmlAttributeIndex;
use Eightfold\HtmlSpec\Read\HtmlAttribute;

class CompilerTest extends PHPUnitTestCase
{
    // TODO: Make this a CLI utility or something
    /**
     * @test
     * @group generate
     */
    public function compiler()
    {
        Compiler::compile();

        $jsonDir   = Compiler::pathPartsToJson();
        $jsonDir[] = "html";
        $jsonDir[] = "textlevel-semantics";
        $jsonDir[] = "a.json";

        $elementPath = implode("/", $jsonDir);

        $actual = file_exists($elementPath);
        $this->assertTrue($actual);

        // Elements
        $actual = HtmlIndex::init()->hasComponentNamed("a");
        $this->assertTrue($actual);

        $actual = HtmlIndex::init()->componentNamed("a");
        $this->assertTrue(is_a($actual, HtmlElement::class));

        // Roles
        $actual = HtmlRolesIndex::init()->hasComponentNamed("any");
        $this->assertTrue($actual);

        $actual = HtmlRolesIndex::init()->componentNamed("alert");
        $this->assertTrue(is_a($actual, HtmlRole::class));

        // Content categories
        $actual = HtmlContentCategoryIndex::init()->hasComponentNamed("flow");
        $this->assertTrue($actual);

        $actual = HtmlContentCategoryIndex::init()->componentNamed("flow");
        $this->assertTrue(is_a($actual, HtmlContentCategory::class));

        // non-ARIA attributes
        $actual = HtmlAttributeIndex::init()->hasComponentNamed("id");
        $this->assertTrue($actual);

        $actual = HtmlAttributeIndex::init()->componentNamed("id");
        $this->assertTrue(is_a($actual, HtmlAttribute::class));
    }

    // /**
    //  * @test
    //  * @group InitialElements
    //  */
    // public function initial_elements()
    // {
    //     Compiler::compileInitialElements();

    //     $json = <<<EOD
    //     {
    //         "name": "h1",
    //         "link": "https://w3c.github.io/html/sections.html#the-h1-element",
    //         "versions": [
    //             "2.0",
    //             "3.2",
    //             "4.01",
    //             "X1.0",
    //             "X1.1",
    //             "5",
    //             "5.1",
    //             "5.2"
    //         ],
    //         "description": "",
    //         "implicit-role": "",
    //         "optional-roles": [],
    //         "interfaces": [],
    //         "categories": {
    //             "self": [],
    //             "parents": [],
    //             "children": []
    //         }
    //     }
    //     EOD;

    //     $expected = json_decode($json);

    //     $actual = HtmlIndex::init()->componentNamed("h1")->component();

    //     $this->assertEquals($expected, $actual);
    // }

    // /**
    //  * @test
    //  * @group InitialRoles
    //  */
    // public function initial_roles()
    // {
    //     Compiler::compileInitialRoles();

    //     $json = <<<EOD
    //     {
    //         "name": "checkbox",
    //         "is-property": false,
    //         "description": "A checkable input that has three possible values: true, false, or mixed.",
    //         "required-properties": [
    //             "aria-checked (state)"
    //         ],
    //         "supported-properties": [
    //             "aria-readonly"
    //         ],
    //         "kind-of-content": "interactive",
    //         "descendent-restrictions": "Flow content, but there must be no interactive content descendant."
    //     }
    //     EOD;

    //     $expected = json_decode($json);

    //     $actual = HtmlRolesIndex::init()->componentNamed("checkbox");

    //     $this->assertEquals($expected, $actual->component());
    // }

    // *
    //  * @test
    //  * @group InitialCategories

    // public function initial_categories()
    // {
    //     Compiler::compileInitialCategories();

    //     $json = <<<EOD
    //     {
    //         "name": "interactive",
    //         "elements": [
    //             "details",
    //             "button",
    //             "label",
    //             "select",
    //             "textarea",
    //             "embed",
    //             "iframe"
    //         ],
    //         "exceptions": "a (if the href attribute is present); audio (if the controls attribute is present); img (if the usemap attribute is present); input (if the type attribute is not in the Hidden state); object (if the usemap attribute is present); video (if the controls attribute is present)"
    //     }
    //     EOD;

    //     $expected = json_decode($json);

    //     $actual = HtmlContentCategoryIndex::init()
    //         ->componentNamed("interactive");

    //     $this->assertEquals($expected, $actual->component());
    // }

    // /**
    //  * @test
    //  * @group InitialAttributes
    //  */
    // public function initial_attributes()
    // {
    //     Compiler::compileInitialAttributes();

    //     $json = <<<EOD
    //     {
    //         "name": "download",
    //         "elements": [
    //             "a",
    //             "area"
    //         ],
    //         "roles": [],
    //         "description": "Whether to download the resource instead of navigating to it, and its file name if so",
    //         "value": "Text",
    //         "categories": [
    //             "other"
    //         ]
    //     }
    //     EOD;

    //     $expected = json_decode($json);

    //     $actual = HtmlAttributeIndex::init()
    //         ->componentNamed("download");

    //     $this->assertEquals($expected, $actual->component());
    // }

    // /**
    //  * @test
    //  * @group Details
    //  */
    // public function element_details()
    // {
    //     Compiler::compileElementDetails();
    // }
}
