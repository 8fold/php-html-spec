<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\Interfaces\IndexReader;
use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;

abstract class AbstractIndex implements IndexReader
{
    protected $index;

    static public function init(): IndexReader
    {
        return new static();
    }

    public function componentNamed(string $name): HtmlComponent
    {
        $parts = Compiler::pathPartsToJson();
        $parts = array_merge($parts, $this->filePathPartsFor($name));
        $path  = implode("/", $parts);
        $type  = $this->type();
        return $type::fromPath($path);
    }

    public function hasComponentNamed(string $name): bool
    {
        $name = $this->componentNamed($name)->name();
        return ! empty($name);
    }

    public function index(): array
    {
        if ($this->index === null) {
            $path        = $this->filePath();
            $json        = file_get_contents($path);
            $this->index = json_decode($json, true);
        }
        return $this->index;
    }

    public function filePath(): string
    {
        $parts = $this->filePathParts();
        $path  = implode("/", $parts);
        return $path;
    }

    public function filePathParts(): array
    {
        $parts   = $this->folderPathParts();
        $parts[] = $this->fileName();
        $path    = implode("/", $parts);
        if (! file_exists($path)) {
            file_put_contents($path, '{}');
        }
        return $parts;
    }

    public function folderPathParts(): array
    {
        return Compiler::pathPartsToJson();
    }
}
