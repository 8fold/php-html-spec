<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;

use Eightfold\HtmlSpecStructured\Write\Interfaces\ComponentWriter;

class HtmlAttribute implements HtmlComponent
{
    private $component;

    static public function fromPath(string $path): ComponentWriter
    {
        $json = file_get_contents($path);
        $component = json_decode($json);
        return static::fromObject($component);
    }

    static public function fromObject(object $component): ComponentWriter
    {
        return new static($component);
    }

    public function __construct(object $component)
    {
        $this->component = $component;
    }

    public function component(): object
    {
        return $this->component;
    }

    public function name(): string
    {
        return $this->component()->name;
    }

    public function elements(): array
    {
        if (isset($this->component()->elements) and
            is_array($this->component()->elements)
        ){
            return $this->component()->elements;
        }
        return [];
    }

    private function misc()
    {
        if (isset($this->component()->misc)) {
            return $this->component()->misc;
        }
        return [];
    }

    public function categories(): array
    {
        return $this->component()->categories;
        // if ($this->isGlobal()) {
        //     return ["global"];

        // } elseif ($this->isBoolean()) {
        //     return ["boolean"];

        // }
        // return ["other"];
    }

    // public function isGlobal(): bool
    // {
    //     $elements = $this->elements();
    //     if (count($elements) !== 1) {
    //         return false;

    //     } elseif ($elements[0] !== "HTML elements" and
    //         $elements[0] !== "HTMLelements"
    //     ) {
    //         return false;
    //     }
    //     return true;
    // }

    // public function isBoolean(): bool
    // {
    //     return ($this->misc() === "Boolean attribute");
    // }

    public function folderPathParts(): array
    {
        return PhpToJson::pathPartsToJson();
    }

    // TODO: Move to Fileable interface - make HtmlComponent and IndexWriter extensions of
    public function filePathParts(): array
    {
        $parts = $this->folderPathParts();

        if (in_array("other", $this->categories())) {
            $parts[] = "html-attributes";

        } elseif (in_array("global", $this->categories())) {
            $parts[] = "html-attributes-global";

        } elseif (in_array("boolean", $this->categories())) {
            $parts[] = "html-attributes-boolean";

        }

        $path = implode("/", $parts);
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $parts[] = "{$this->name()}.json";
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
        $filePath = $this->filePathParts();

        $relativePathParts = array_diff($filePath, $jsonPathParts);

        return array_values($relativePathParts);
    }

    public function fileName(): string
    {
        return "{$this->name()}.json";
    }
}
