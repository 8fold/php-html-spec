<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use Eightfold\HtmlSpec\Read\HtmlContentCategory as HtmlContentCategoryReader;

class HtmlContentCategory extends HtmlContentCategoryReader
{
    public function save(): HtmlContentCategory
    {
        $path      = $this->filePath();
        $component = $this->component();
        $json      = json_encode($component, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);

        return $this;
    }
}
