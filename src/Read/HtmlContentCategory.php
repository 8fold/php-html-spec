<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\AbstractComponent;

use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;
use Eightfold\HtmlSpecStructured\Read\Traits\HtmlComponentImp;

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
