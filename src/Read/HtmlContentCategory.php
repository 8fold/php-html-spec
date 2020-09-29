<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\AbstractComponent;

use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;
use Eightfold\HtmlSpec\Read\Traits\HtmlComponentImp;

class HtmlContentCategory extends AbstractComponent
{
    const TEMPLATE = [
        "name"       => "",
        "elements"   => [],
        "exceptions" => ""
    ];

    public function subFolder(): string
    {
        return "html-content-categories";
    }

    public function folderPathParts(): array
    {
        $parts   = Compiler::pathPartsToJson();
        $parts[] = $this->subFolder();

        $path = implode("/", $parts);
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $parts;
    }
}
