<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\HtmlRole as HtmlRoleReader;

use Eightfold\HtmlSpecStructured\Write\HtmlIndex;
use Eightfold\HtmlSpecStructured\Write\HtmlAttribute;

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
