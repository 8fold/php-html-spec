<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlAttribute as HtmlAttributeReader;

use Eightfold\HtmlSpec\Write\Interfaces\ComponentWriter;

class HtmlAttribute extends HtmlAttributeReader implements ComponentWriter
{
    public function save(): ComponentWriter
    {
        $path = $this->filePath();
        $component = $this->component();
        $json = json_encode($component, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }
}
