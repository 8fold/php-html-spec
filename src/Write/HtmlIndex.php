<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlIndex as HtmlIndexReader;

use Eightfold\HtmlSpec\Write\HtmlElement;

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

    static public function storeDetails()
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
                while ($node->nodeName !== "table") {
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
            $isCorrectHeading = false;
            if ($node->nextSibling->nextSibling !== null) {
                $isCorrectHeading = $node->nextSibling->nextSibling->nodeValue !== "Semantics";
            }

            if ($isElementsHeading and $isCorrectHeading) {
                // TODO: DRY break
                while ($node->nodeName !== "table") {
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

            $descriptNode = $cells[0];
            $description  = strip_tags(trim($descriptNode->nodeValue));

            $catNode       = $cells[1];
            $categories = strip_tags(trim($catNode->nodeValue));
            $categories = explode(";", $categories);
            $categories = array_map(function($v) {
                $v = trim($v);
                return str_replace("*", "", $v);
            }, $categories);

            $parentNode    = $cells[2];
            $parents = strip_tags(trim($parentNode->nodeValue));
            $parents = explode(";", $parents);
            $parents = array_map(function($v) {
                $v = trim($v);
                return str_replace("*", "", $v);
            }, $parents);

            $childrenNode  = $cells[3];
            $children = strip_tags(trim($childrenNode->nodeValue));
            $children = explode(";", $children);
            $children = array_map(function($v) {
                $v = trim($v);
                return str_replace("*", "", $v);
            }, $children);

            $elem = $row->getElementsByTagName("th");
            $elem = $elem[0];
            $elem = $elem->nodeValue;
            $elem = strip_tags($elem);

            $elems = explode(",", $elem);
            foreach ($elems as $e) {
                $e = trim($e);

                if (HtmlIndex::init()->hasComponentNamed($e)) {
                    // TODO: Interfaces
                    // $interfaces = $interfaces[$e];

                    $e = HtmlIndex::init()->componentNamed($e);
                    $e = $e->component();

                    $e->description          = $description;
                    $e->categories->self     = $categories;
                    $e->categories->parents  = $parents;
                    $e->categories->children = $children;

                    $e = HtmlElement::fromObject($e);
                    $e->save();
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
