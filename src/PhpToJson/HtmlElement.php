<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class HtmlElement
{
    private $element;

    static public function storeHtmlElements()
    {
        $elementList = static::elementsListJson();
        foreach ($elementList as $element) {
            $element = static::fromObject($element);
            $path = $element->folderPath();
            if (! file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $filePath = $element->filePath();

            PhpToJson::updateHtmlElementIndex($element);

            $content = json_encode($element->element(), JSON_PRETTY_PRINT);
            file_put_contents($filePath, $content);
        }
    }

    static private function elementsListJson()
    {
        return json_decode(
            PhpToJson::curlContent("https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json")
        );
    }

    static public function pathToHtmlElementIndex()
    {
        return PhpToJson::pathToHtml() ."/index.json";
    }

    static private function elementList()
    {
        $json = file_get_contents(static::pathToHtmlElementIndex());
        $list = json_decode($json, true);
        return array_keys($list);
    }

    static public function updateElementAttributes()
    {
        foreach (scandir(HtmlAttribute::folderPathToHtmlAttributes()) as $file) {
            $fileParts = explode(".", $file, 2);
            if (isset($fileParts[1]) and $extension = $fileParts[1] === "json") {
                $parts = HtmlAttribute::folderPathPartsToHtmlAttributes();
                $parts[] = $file;
                $path = implode("/", $parts);
                $json = file_get_contents($path);
                $attrName = $fileParts[0];
                $attr = json_decode($json);
                foreach ($attr->{$attrName}->elements as $elementName) {
                    if ($elementName = "HTML elements") {
                        static::registerGlobalAttributes($attrName);

                    } else {
                        HtmlElement::named($elementName)
                            ->addAttribute($attrName)->save();

                    }
                }
            }
        }
    }
// TODO: non-conforming features for obsolete elements
    static public function updateElementDetails()
    {
        die(var_dump(
            PhpToJson::specSourceDom()
                ->getElementById("elements-3")
        ));
    }

    static private function registerGlobalAttributes(string $attrName): void
    {
        foreach (static::elementList() as $element) {
            static::named($element)->addAttribute($attrName)->save();

        }
    }

    static public function named(string $elementName): HtmlElement
    {
        $path = static::pathToHtmlElementIndex();
        $json = file_get_contents($path);
        $index = json_decode($json);
        if (isset($index->{$elementName})) {
            $parts = PhpToJson::pathPartsToJson();
            $parts = array_merge($parts, $index->{$elementName});
            $path = implode("/", $parts);
            return HtmlElement::fromPath($path);
        }
        trigger_error("Element ({$elementName}) not found.");
    }

    static public function fromPath(string $path): HtmlElement
    {
        $json = file_get_contents($path);
        $element = json_decode($json);
        return HtmlElement::fromObject($element);
    }

    static public function fromObject(object $element): HtmlElement
    {
        if ($element === null) {
            trigger_error("Element cannot be null or empty: ". __FUNCTION__);
        }
        return new HtmlElement($element);
    }

    public function __construct(object $element)
    {
        if ($element === null) {
            trigger_error("Element cannot be null: ". __FUNCTION__);
        }
        $this->element = $element;
    }

    public function addAttribute(string $attrName): HtmlElement
    {
        if (! isset($this->element()->attributes)) {
            $this->element()->attributes = [];
        }
        $this->element()->attributes[] = $attrName;
        $unique = array_unique($this->element()->attributes);
        $reindexed = array_values($unique);
        $this->element()->attributes = $reindexed;

        return $this;
    }

    public function save(): HtmlElement
    {
        $path = $this->filePath();
        $json = json_encode($this->element(), JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }

    public function element()
    {
        return $this->element;
    }

    public function name(): string
    {
        return $this->element()->element;
    }

    public function fileName(): string
    {
        return $this->name() .".json";
    }

    public function filePath(): string
    {
        $filePath = $this->folderPathParts();
        $filePath[] = $this->fileName();
        $filePath = implode("/", $filePath);
        return $filePath;
    }

    public function folderPath(): string
    {
        $folderPathParts = $this->folderPathParts();
        $folderPath = implode("/", $folderPathParts);
        return $folderPath;
    }

    public function filePathPartsRelative(): array
    {
        $jsonPathParts = PhpToJson::pathPartsToJson();
        $filePath = $this->folderPathParts();

        $relativePathParts = array_diff($filePath, $jsonPathParts);
        $relativePathParts[] = $this->fileName();

        return $relativePathParts;
    }

    private function folderPathParts(): array
    {
        $link = parse_url($this->element()->link);

        $dirPath = explode(".", $link["path"], 2);
        $path = array_shift($dirPath);
        $path = array_filter(explode("/", $path));

        $dirParts = PhpToJson::pathPartsToJson();

        $folderPathParts = array_merge($dirParts, $path);

        return $folderPathParts;
    }
}
