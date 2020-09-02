<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\HtmlElement as HtmlElementReader;

use Eightfold\HtmlSpecStructured\Write\HtmlIndex;
use Eightfold\HtmlSpecStructured\Write\HtmlAttribute;

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
