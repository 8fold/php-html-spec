<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use \ArrayAccess;
use \Iterator;

use Eightfold\HtmlSpecStructured\PhpToJson;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlIndex as HtmlIndexWriter;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElement;
// use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class HtmlAttributeIndex // implements ArrayAccess, Iterator
{
    protected $index;

    protected $components;

    static public function all()
    {
        return new static();
    }

    static private function pathParts()
    {
        return PhpToJson::pathPartsToJson();
    }

    // static public function path()
    // {
    //     $parts = static::pathParts();
    //     return implode("/", $parts);
    // }

    // public function pathPartsFor(string $name, string $in = "any"): array
    // {
    //     $index = [];
    //     if ($in === "any") {
    //         $index = $this->indexFor();
    //     }
    //     var_dump(__FILE__);
    //     var_dump(__LINE__);
    //     var_dump($name);
    //     return $index[$name];
    // }

    public function index(): array
    {
        if ($this->index === null) {
            $this->index = [];
            $pathParts   = static::pathParts();

            $otherParts = $pathParts;
            $folderPath = implode("/", $otherParts);
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            $otherParts[] = "html-attributes.json";

            $otherPath = implode("/", $otherParts);
            if (! file_exists($otherPath)) {
                file_put_contents($otherPath, '{}');
            }
            $json        = file_get_contents($otherPath);
            $this->index = json_decode($json, true);
        }
        return $this->index;
    }

    public function components(): array
    {
        if ($this->components === null) {
            $index = $this->index();
            $build = [];
            foreach ($index as $category => $attributes) {
                $build = array_merge($build, (array) $attributes);
            }
            $this->components = $build;
        }
        return $this->components;
    }

    public function componentNames(): array
    {
        return array_keys($this->components());
    }

    public function componentNamed(string $name): HtmlElement
    {
        $parts = PhpToJson::pathPartsToJson();
        $parts = array_merge($parts, $this->pathPartsFor($name));
        $path  = implode("/", $parts);
        return HtmlElement::fromPath($path);
    }

    // TODO: Move to Fileable interface - make HtmlComponent and IndexWriter extensions of
    public function filePathParts(): array
    {
        $parts = PhpToJson::pathPartsToJson();
        $parts[] = "html-attributes.json";
        return $parts;
    }

    // TODO: Move to Fileable interface - make HtmlComponent and IndexWriter extensions of
    public function filePath(): string
    {
        $parts = $this->filePathParts();
        return implode("/", $parts);
    }

    public function filePathPartsRelative(): array
    {
        $jsonPathParts = PhpToJson::pathPartsToJson();
        $filePath = $this->folderParts();

        $relativePathParts = array_diff($filePath, $jsonPathParts);
        $relativePathParts[] = $this->fileName();

        return $relativePathParts;
    }

    public function pathPartsFor(string $name): array
    {
        $components = $this->components();
        return $components[$name];
    }

// - ArrayAccess

//     public function offsetExists($offset): bool
//     {
//         $index = $this->index();
//         if (isset($index[$offset]) and
//             ! is_a($index[$offset], HtmlElement::class)
//         ) {
//             $element = $this->elementNamed($offset);
//             $this->elements[$offset] = $element;

//         }

//         return (isset($index[$offset]) and
//             is_a($this->elements[$offset], HtmlElement::class));
//     }

//     public function offsetGet($offset): HtmlElement
//     {
//         if ($this->offsetExists($offset)) {
//             return $this->elements[$offset];
//         }
//         trigger_error("Could not find element {$offset}.");
//     }

//     public function offsetSet($offset, $value): void
//     {
//         die("offset set");
//     }

//     public function offsetUnset($offset): void
//     {
//         die("offset unset");
//     }

// // - Iterator
//     public function rewind()
//     {
//         reset($this->elements);
//     }

//     public function current()
//     {
//         return current($this->elements);
//     }

//     public function key()
//     {
//         return key($this->elements);
//     }

//     public function next()
//     {
//         next($this->elements);
//     }

//     public function valid() {
//         return key($this->elements) !== null;
//     }
}
