<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\Read\HtmlIndex as HtmlIndexReader;

use Eightfold\HtmlSpecStructured\PhpToJson;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElement;

class HtmlIndex extends HtmlIndexReader
{
    static public function storeInitial(): void
    {
        $index = static::all();

        $json = PhpToJson::curlContent("https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json");
        $elementList = json_decode($json);
        foreach ($elementList as $element) {
            $element = HtmlElement::fromObject($element);

            $elem    = $element->element();
            $content = json_encode($elem, JSON_PRETTY_PRINT);
            $path    = $element->filePath();

            $element->save();

            $index->addElement($element);
        }
        $index->save();
    }

    public function storeDetails()
    {
        $elements = static::all();

        // DOM
        $headers = PhpToJson::specSourceDom()->getElementsByTagName("h3");

        // Interfaces, with inheritance
        $interfaces = [];
        for ($i = 0; $i < count($headers); $i++) {
            $node = $headers[$i];
            $isInterfacesHeading = $node->nodeValue === "Element Interfaces";
            if ($isInterfacesHeading) {
                while ($node->tagName !== "table") {
                    $node = $node->nextSibling;
                }
                $table = $node;

                $tBody = $table->getElementsByTagName("tbody");
                $tBody = $tBody[0];
                $rows  = $tBody->getElementsByTagName("tr");

                for ($r = 0; $r < count($rows); $r++) {
                    if ($row = $rows[$r] and $row->tagName === "tr") {
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
                }
            }
        }

        // Main details
        for ($i = 0; $i < count($headers); $i++) {
            $node = $headers[$i];
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
                $rows  = $tBody->getElementsByTagName("tr");

                for ($r = 0; $r < count($rows); $r++) {
                    if ($row = $rows[$r] and $row->tagName === "tr") {
                        $elementName = $row->getElementsByTagName("th");
                        $elementName = $elementName[0];
                        $elementName = $elementName->getElementsByTagName("code");
                        $elementName = $elementName[0];
                        $elementName = $elementName->nodeValue;

                        if (isset($elements[$elementName])) {
                            $element = $elements[$elementName];

                            $cells = $row->getElementsByTagName("td");
                            $descriptNode  = $cells[0];
                            $catNode       = $cells[1];
                            $parentNode    = $cells[2];
                            $childrenNode  = $cells[3];
                            $interfaceNode = $cells[5];

                            $description = strip_tags(trim($descriptNode->nodeValue));

                            $categories = strip_tags(trim($catNode->nodeValue));
                            $categories = explode(";", $categories);
                            $categories = array_map(function($v) {
                                $v = trim($v);
                                return str_replace("*", "", $v);
                            }, $categories);

                            // TODO: DRY with previous
                            $parents = strip_tags(trim($parentNode->nodeValue));
                            $parents = explode(";", $parents);
                            $parents = array_map(function($v) {
                                $v = trim($v);
                                return str_replace("*", "", $v);
                            }, $parents);

                            $children = strip_tags(trim($childrenNode->nodeValue));
                            $children = explode(";", $children);
                            $children = array_map(function($v) {
                                $v = trim($v);
                                return str_replace("*", "", $v);
                            }, $children);

                            $interface = $interfaces[$elementName];

                            $element->addDescription($description);
                            $element->addCategories($categories);
                            $element->addParents($parents);
                            $element->addChildren($children);
                            $element->addInterfaces($interface);
                        }
                    }
                }
                break;
            }
        }
        $elements->saveElements();
    }

    static public function storeAttributes(): void
    {
        $elements = static::all();
        foreach (HtmlAttribute::all() as $attribute) {
            $attrName = $attribute->name;
            $elem = $attribute->elements;
            foreach ($elem as $elementsForAttribute) {
                $elementNames = ($elementsForAttribute === "HTML elements" or
                    $elementsForAttribute === "HTMLelements"
                )
                    ? $elements->elementNames()
                    : [$elementsForAttribute];

                foreach ($elementNames as $elementName) {
                    $element = $elements[$elementName];
                    $element->addAttribute($attrName);
                }
            }
        }
        $elements->saveElements();
    }

    public function addElement(HtmlElement $element): HtmlIndex
    {
        $name = $element->name();
        $fileParts = $element->filePartsRelative();

        $this->index[$name] = array_values($fileParts);

        return $this;
    }

    public function save(): HtmlIndex
    {
        $json = json_encode($this->index, JSON_PRETTY_PRINT);
        file_put_contents(static::path(), $json);

        return $this;
    }

    public function saveElements(): HtmlIndex
    {
        foreach ($this->elements() as $element) {
            if (is_a($element, HtmlElement::class)) {
                $element->save();

            }
        }
        return $this;
    }
}
