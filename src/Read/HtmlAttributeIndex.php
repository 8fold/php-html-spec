<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use \ArrayAccess;
use \Iterator;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\AbstractIndex;

use Eightfold\HtmlSpecStructured\Read\HtmlAttribute;

use Eightfold\HtmlSpecStructured\Write\HtmlIndex as HtmlIndexWriter;

class HtmlAttributeIndex extends AbstractIndex
{
    public function fileName(): string
    {
        return "html-attributes.json";
    }

    public function type(): string
    {
        return HtmlAttribute::class;
    }

    public function componentNames(): array
    {
        $index = $this->index();
        $names = array_keys($index);
        return $names;
    }

    public function filePathPartsFor(string $name): array
    {
        $index = $this->index();
        foreach ($index as $category => $attributes) {
            if (isset($attributes[$name])) {
                return $attributes[$name];

            }
        }
        return [];
    }
}
