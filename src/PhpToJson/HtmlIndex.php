<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use \ArrayAccess;
use \Iterator;

use Eightfold\HtmlSpecStructured\PhpToJson;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElement;
// use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class HtmlIndex implements ArrayAccess, Iterator
{
    private $index;

    private $elements;

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
                            }, $parents);

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

    // TODO: PHP 8 - bool -> array|object
    static public function all()
    {
        return new static();
    }

    static public function path(): string
    {
        $parts = PhpToJson::pathPartsToJson();
        $parts[] = "html";
        $parts[] = "index.json";

        $path = implode("/", $parts);
        if (! file_exists($path)) {
            file_put_contents($path, '{}');
        }
        return $path;
    }

    public function __construct()
    {
        $json = file_get_contents(static::path());
        $this->index = (array) json_decode($json);

        $keys = $this->elementNames();
        $this->elements = array_flip($keys);
    }

    public function index(): array
    {
        return $this->index;
    }

    public function elementNames(): array
    {
        return array_keys($this->index);
    }

    public function elements(): array
    {
        return $this->elements;
    }

    public function elementNamed(string $name): HtmlElement
    {
        $parts = PhpToJson::pathPartsToJson();
        $index = $this->index();
        $parts = array_merge($parts, $index[$name]);
        $path = implode("/", $parts);
        return HtmlElement::fromPath($path);
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
        foreach ($this->elements as $element) {
            if (is_a($element, HtmlElement::class)) {
                $element->save();

            }
        }
        return $this;
    }

// - ArrayAccess

    public function offsetExists($offset): bool
    {
        if (isset($this->index[$offset]) and
            ! is_a($this->index[$offset], HtmlElement::class)
        ) {
            $element = $this->elementNamed($offset);
            $this->elements[$offset] = $element;

        }

        return (isset($this->index[$offset]) and
            is_a($this->elements[$offset], HtmlElement::class));
    }

    public function offsetGet($offset): HtmlElement
    {
        if ($this->offsetExists($offset)) {
            return $this->elements[$offset];
        }
        trigger_error("Could not find element {$offset}.");
    }

    public function offsetSet($offset, $value): void
    {
        die("offset set");
    }

    public function offsetUnset($offset): void
    {
        die("offset unset");
    }

// - Iterator
    public function rewind()
    {
        reset($this->elements);
    }

    public function current()
    {
        return current($this->elements);
    }

    public function key()
    {
        return key($this->elements);
    }

    public function next()
    {
        next($this->elements);
    }

    public function valid() {
        return key($this->elements) !== null;
    }
}
