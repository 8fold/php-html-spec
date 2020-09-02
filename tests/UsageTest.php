<?php

namespace Eightfold\HtmlSpecStructured\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\HtmlIndex;
use Eightfold\HtmlSpecStructured\Read\HtmlElement;

use Eightfold\HtmlSpecStructured\Read\HtmlRolesIndex;
use Eightfold\HtmlSpecStructured\Read\HtmlRole;

use Eightfold\HtmlSpecStructured\Read\HtmlContentCategoryIndex;
use Eightfold\HtmlSpecStructured\Read\HtmlContentCategory;

use Eightfold\HtmlSpecStructured\Read\HtmlAttributeIndex;
use Eightfold\HtmlSpecStructured\Read\HtmlAttribute;

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

        $jsonDir = Compiler::pathPartsToJson();
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
}
