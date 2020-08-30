<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use \ArrayAccess;
use \Iterator;

use Eightfold\HtmlSpecStructured\PhpToJson;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlIndex as HtmlIndexWriter;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElement;
// use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class HtmlIndex implements ArrayAccess, Iterator
{
    private $index;

    private $elements;

    static public function all()
    {
        return new static();
    }

    static private function pathParts()
    {
        $parts   = PhpToJson::pathPartsToJson();
        $parts[] = "html.json";
        $path    = implode("/", $parts);
        if (! file_exists($path)) {
            file_put_contents($path, '{}');
        }
        return $parts;
    }

    static public function path()
    {
        $parts = static::pathParts();
        return implode("/", $parts);
    }

    public function pathPartsFor(string $elementName): array
    {
        $index = $this->index();
        return $index[$elementName];
    }

    private function index(): array
    {
        if ($this->index === null) {
            $path        = static::path();
            $json        = file_get_contents($path);
            $this->index = (array) json_decode($json);
        }
        return $this->index;
    }

    public function elements(): array
    {
        if ($this->elements === null) {
            $keys = $this->elementNames();
            $this->elements = array_flip($keys);
        }
        return $this->elements;
    }

    public function elementNamed(string $name): HtmlElement
    {
        $parts = PhpToJson::pathPartsToJson();
        $parts = array_merge($parts, $this->pathPartsFor($name));
        $path  = implode("/", $parts);
        return HtmlElement::fromPath($path);
    }

    public function elementNames(): array
    {
        return array_keys($this->index());
    }

// - ArrayAccess

    public function offsetExists($offset): bool
    {
        $index = $this->index();
        if (isset($index[$offset]) and
            ! is_a($index[$offset], HtmlElement::class)
        ) {
            $element = $this->elementNamed($offset);
            $this->elements[$offset] = $element;

        }

        return (isset($index[$offset]) and
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
