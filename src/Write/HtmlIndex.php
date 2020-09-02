<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\HtmlIndex as HtmlIndexReader;

use Eightfold\HtmlSpecStructured\Write\HtmlElement;

class HtmlIndex extends HtmlIndexReader
{
    static public function storeInitial(): void
    {
        $index = static::init();

        $elementList = Compiler::htmlElementList();
        foreach ($elementList as $element) {
            $template = HtmlElement::TEMPLATE;

            $multiple = explode(",", $element->element);
            foreach ($multiple as $elem) {
                $template["name"]     = $elem;
                $template["link"]     = $element->link;
                $template["versions"] = $element->specs;

                $element = (object) $template;
                $element = HtmlElement::fromObject($element);
                if ($element->linkSubFolder() === "html") {
                    $element->save();

                    $index->addComponent($element);
                }
            }
        }
        $index->save();
    }

    public function storeDetails()
    {
        $index = static::init();

        // DOM
        $headers = Compiler::specSourceDom()->getElementsByTagName("h3");

        // Interfaces, with inheritance
        $rows = [];
        foreach ($headers as $node) {
            $table;
            $isInterfacesHeading = $node->nodeValue === "Element Interfaces";
            if ($isInterfacesHeading) {
                while ($node->tagName !== "table") {
                    $node = $node->nextSibling;

                }
                $table = $node;

                $tBody = $table->getElementsByTagName("tbody");
                $tBody = $tBody[0];

                $rows = $tBody->getElementsByTagName("tr");
            }
        }

        $interfaces = [];
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName("td");

            $elemCell = $cells[0];
            $elemCell = strip_tags($elemCell->nodeValue);
            $elemName = trim($elemCell);

            $interfaceCell  = $cells[1];
            $interfaceCell  = trim($interfaceCell->nodeValue);
            $interfaceCell  = explode(":", $interfaceCell);
            $elemInterfaces = array_map("trim", $interfaceCell);

            $interfaces[$elemName] = $elemInterfaces;
        }

        // Main details
        $rows = [];
        foreach ($headers as $node) {
            $isElementsHeading = $node->nodeValue === "Elements";
            $isCorrectHeading = $node->nextSibling->nextSibling->nodeValue !== "Semantics";

            if ($isElementsHeading and $isCorrectHeading) {
                // TODO: DRY break
                while ($node->tagName !== "table") {
                    $node = $node->nextSibling;
                }
                $table = $node;

                // TODO: DRY break
                $tBody = $table->getElementsByTagName("tbody");
                $tBody = $tBody[0];

                $rows = $tBody->getElementsByTagName("tr");
            }
        }

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName("td");

            $elementName = $row->getElementsByTagName("th");
            $elementName = $elementName[0];
            $elementName = $elementName->nodeValue;
            $elementName = strip_tags($elementName);

            $multiple = explode(",", $elementName);
            foreach ($multiple as $elem) {
                $elem = trim($elem);
                if (HtmlIndex::init()->hasComponentNamed($elem)) {
                    $element = HtmlIndex::init()->componentNamed($elem);
                    $element = $element->component();

                    $descriptNode  = $cells[0];
                    $catNode       = $cells[1];
                    $parentNode    = $cells[2];
                    $childrenNode  = $cells[3];
                    $interfaceNode = $cells[5];

                    $element->description = strip_tags(trim($descriptNode->nodeValue));

                    $categories = strip_tags(trim($catNode->nodeValue));
                    $categories = explode(";", $categories);
                    $element->categories->self = array_map(function($v) {
                        $v = trim($v);
                        return str_replace("*", "", $v);
                    }, $categories);

                    // TODO: DRY with previous
                    $parents = strip_tags(trim($parentNode->nodeValue));
                    $parents = explode(";", $parents);
                    $element->categories->parents = array_map(function($v) {
                        $v = trim($v);
                        return str_replace("*", "", $v);
                    }, $parents);

                    $children = strip_tags(trim($childrenNode->nodeValue));
                    $children = explode(";", $children);
                    $element->categories->children = array_map(function($v) {
                        $v = trim($v);
                        return str_replace("*", "", $v);
                    }, $children);

                    $element->interfaces = $interfaces[$elementName];

                    $element = HtmlElement::fromObject($element);

                    $element->save();
                }
            }
        }
    }

    public function addComponent(HtmlElement $element): HtmlIndex
    {
        $name      = $element->name();
        $fileParts = $element->filePathPartsRelative();
        $fileParts = array_values($fileParts);
        $category  = $fileParts[1];

        $this->index[$category][$name] = $fileParts;

        return $this;
    }

    public function save(): HtmlIndex
    {
        $index = $this->index();
        ksort($index, SORT_NATURAL);

        $json = json_encode($index, JSON_PRETTY_PRINT);
        $path = $this->filePath();

        file_put_contents($path, $json);

        return $this;
    }
}
