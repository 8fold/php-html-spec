<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\AbstractComponent;

use Eightfold\HtmlSpecStructured\Read\HtmlIndex;
use Eightfold\HtmlSpecStructured\Read\HtmlAttribute;
use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;
// use Eightfold\HtmlSpecStructured\Read\Traits\HtmlComponentImp;

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
