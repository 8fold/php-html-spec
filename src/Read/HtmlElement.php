<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\AbstractComponent;

use Eightfold\HtmlSpec\Read\HtmlIndex;
use Eightfold\HtmlSpec\Read\HtmlAttribute;
use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;

class HtmlElement extends AbstractComponent
{
    const TEMPLATE = [
        "name"           => "",
        "link"           => "",
        "versions"       => "",
        "description"    => "",
        "implicit-role"  => "",
        "optional-roles" => [],
        "interfaces"     => [],
        "categories"     => [
            "self"     => [],
            "parents"  => [],
            "children" => []
        ]
    ];

    public function acceptsChildren(): bool
    {
        return ! ($this->categories()->children[0] === "empty");
    }

    public function categories(): object
    {
        return $this->component()->categories;
    }

    // TODO: categoryArray - ??

    public function link(): string
    {
        return $this->component()->link;
    }

    public function linkSubFolder(): string
    {
        $link = $this->link();
        $parts = explode("/", $link);
        array_pop($parts);
        $subFolder = array_pop($parts);
        return $subFolder;
    }

    public function linkGroup(): string
    {
        $link  = $this->link();
        $parts = explode("/", $link);
        $last  = array_pop($parts);
        $parts = explode(".", $last, 2);
        $first = array_shift($parts);
        return $first;
    }

    public function filePathRelative(): string
    {
        $parts = $this->filePathPartsRelative();
        $path  = implode("/", $parts);
        return $path;
    }

    public function folderPathParts(): array
    {
        $parts   = Compiler::pathPartsToJson();
        $parts[] = $this->linkSubFolder();
        $parts[] = $this->linkGroup();
        $path    = implode("/", $parts);

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        return $parts;
    }
}
