<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAbstract;

use Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlIndex;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

// TODO: Consider separating out write functionality to separate class; so,
//      this class can be used for read operations safely.
class HtmlElement extends HtmlAbstract
{
    private $element;

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

    public function addDescription(string $description): HtmlElement
    {
        return $this->addStringAt($description, "description");
    }

    public function addInterfaces(array $interfaces): HtmlElement
    {
        return $this->addArrayAt($interfaces, "interfaces");
    }

    public function addCategories(array $categories): HtmlElement
    {
        return $this->addArrayAt($categories, "categories");
    }

    public function addParents(array $parents): HtmlElement
    {
        return $this->addArrayAt($parents, "parents");
    }

    public function addChildren(array $children): HtmlElement
    {
        return $this->addArrayAt($children, "children");
    }

    private function addStringAt(string $string, string $key): HtmlElement
    {
        $this->element()->{$key} = $string;
        return $this;
    }

    private function addArrayAt(array $array, string $key): HtmlElement
    {
        $this->element()->{$key} = $array;
        return $this;
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
        $element = $this->element();
        $json = json_encode($element, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }

    // TODO: definition
    public function element(): object
    {
        return $this->definition();
    }

    public function definition(): object
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
        $filePath = $this->folderParts();
        $filePath[] = $this->fileName();
        $filePath = implode("/", $filePath);
        if (! file_exists($filePath)) {
            file_put_contents($filePath, '{}');
        }
        return $filePath;
    }

    public function folder(): string
    {
        $folderParts = $this->folderParts();
        return implode("/", $folderParts);
    }

    public function filePartsRelative(): array
    {
        $jsonPathParts = PhpToJson::pathPartsToJson();
        $filePath = $this->folderParts();

        $relativePathParts = array_diff($filePath, $jsonPathParts);
        $relativePathParts[] = $this->fileName();

        return $relativePathParts;
    }

    private function folderParts(): array
    {
        $link = parse_url($this->element()->link);

        $dirPath = explode(".", $link["path"], 2);
        $path = array_shift($dirPath);
        $path = array_filter(explode("/", $path));

        $dirParts = PhpToJson::pathPartsToJson();

        $folderParts = array_merge($dirParts, $path);
        $folderPath = implode("/", $folderParts);
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        return $folderParts;
    }
}
