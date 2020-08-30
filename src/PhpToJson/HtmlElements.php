<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use \ArrayAccess;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAbstract;

use Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlIndex;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElement;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class HtmlElements extends HtmlAbstract implements ArrayAccess
{
    const SUB_FOLDER_NAME = "html";

    static public function updateElementAttributes(): void
    {
        $elements = HtmlIndex::all();
        foreach (scandir(HtmlAttribute::folderPath()) as $file) {
            $fileParts = explode(".", $file, 2);
            if (isset($fileParts[1]) and $extension = $fileParts[1] === "json") {
                die(var_dump(
                    $fileParts[0]
                ));
                $parts = HtmlAttribute::folderPathParts();
                $parts[] = $file;

                $path = implode("/", $parts);
                $json = file_get_contents($path);
                $attrName = $fileParts[0];
                $attr = json_decode($json);
                foreach ($attr->{$attrName}->elements as $elementName) {
                    $elementNames = [$elementName];
                    if ($elementNames = "HTML elements") {
                        $elementNames = $elements->elementNames();

                    }

                    foreach ($elementNames as $name) {
                        $elements->named($name)->addAttribute($attrName);

                    }
                }
            }
        }
        $elements->save();
    }

    public function __construct(array $initial)
    {
        $this->elementList = $initial;
    }

    public function elementNames(): array
    {
        // return array_keys($this->elementList);
    }

    public function named(string $name): HtmlElement
    {
        // return $this[$name];
    }

    public function save(): void
    {
        foreach ($this->elementList as $elementName => $element) {
            if (is_a($element, HtmlElement::class)) {
                $element->save();

            } elseif ($this->offsetExists($elementName)) {

                var_dump($elementName);

            } else {
                var_dump("not in element list");
                var_dump($elementName);

            }
        }
    }

// - ArrayAccess

    public function offsetExists($offset): bool
    {
        // if (! isset($this->elementList[$offset])) {
        //     die("Offset does not exist: {$offset}");
        // }
        // return isset($this->elementList[$offset]);
    }

    public function offsetGet($offset): HtmlElement
    {
        // if ($this->offsetExists($offset) and
        //     ! is_a($this->elementList[$offset], HtmlElement::class)
        // ) {
        //     $this->offsetSet($offset, HtmlElement::named($offset));
        // }
        // return $this->elementList[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        // $this->elementList[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        die("offsetUnset");
    }
}
