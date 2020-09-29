<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlRole as HtmlRoleReader;

use Eightfold\HtmlSpec\Write\HtmlIndex;
use Eightfold\HtmlSpec\Write\HtmlAttribute;

class HtmlRole extends HtmlRoleReader
{
    public function save(): HtmlRole
    {
        $path      = $this->filePath();
        $component = $this->component();
        // TODO: Consider saving a non-pretty-print version (min) - ??
        $json      = json_encode($component, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }
}
