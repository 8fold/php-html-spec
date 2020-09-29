<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlElement as HtmlElementReader;

use Eightfold\HtmlSpec\Write\HtmlIndex;
use Eightfold\HtmlSpec\Write\HtmlAttribute;

class HtmlElement extends HtmlElementReader
{
    public function save(): HtmlElement
    {
        $path      = $this->filePath();
        $component = $this->component();
        // TODO: Consider saving a non-pretty-print version (min) - ??
        $json      = json_encode($component, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }
}
