<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write;

use Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\Write\Interfaces\ComponentWriter;
use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;

class HtmlAttribute implements ComponentWriter, HtmlComponent
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
        $array = (array) $component;
        $array = array_values($array);
        $this->component = (object) $array[0];
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
        $elements = $this->elements();
        if (count($elements) === 1 or
            ($elements[0] === "HTML elements" or $elements[0] === "HTMLelements")
        ) {
            return ["global"];

        } elseif ($this->misc() === "Boolean attribute") {
            return ["boolean"];

        }
        return ["other"];
    }

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

    public function save(): HtmlComponent
    {
        $path = $this->filePath();
        $component = $this->component();
        $json = json_encode($component, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }
}
