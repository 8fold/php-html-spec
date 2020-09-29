<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\AbstractComponent;

use Eightfold\HtmlSpec\Read\HtmlIndex;
use Eightfold\HtmlSpec\Read\HtmlAttribute;
use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;
// use Eightfold\HtmlSpec\Read\Traits\HtmlComponentImp;

class HtmlRole extends AbstractComponent
{
    const TEMPLATE = [
        "name"                    => "",
        "is-property"             => "",
        "description"             => "",
        "required-properties"     => [],
        "supported-properties"    => [],
        "kind-of-content"         => "",
        "descendent-restrictions" => ""
    ];

    public function folderPathParts(): array
    {
        $parts   = Compiler::pathPartsToJson();
        $parts[] = "html-roles";

        $path = implode("/", $parts);
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $parts;
    }
}
