<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlIndex;
use Eightfold\HtmlSpec\Read\HtmlAttribute;
use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;

abstract class AbstractComponent implements HtmlComponent
{
    protected $component;

    static public function fromPath(string $path): HtmlComponent
    {
        $json   = file_get_contents($path);
        $object = json_decode($json);
        return static::fromObject($object);
    }

    static public function fromObject(object $component): HtmlComponent
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

    public function fileName(): string
    {
        $name = $this->name();
        return "{$name}.json";
    }

    public function filePath(): string
    {
        $parts = $this->filePathParts();
        $path  = implode("/", $parts);
        return $path;
    }

    public function filePathPartsRelative(): array
    {
        $baseParts = Compiler::pathPartsToJson();
        $parts     = $this->filePathParts();
        $parts     = array_diff($parts, $baseParts);
        return $parts;
    }

    public function filePathParts(): array
    {
        $parts   = $this->folderPathParts();
        $parts[] = $this->fileName();
        $filePath = implode("/", $parts);
        if (! file_exists($filePath)) {
            file_put_contents($filePath, '{}');
        }
        return $parts;
    }
}
