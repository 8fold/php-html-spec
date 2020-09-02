<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\AbstractIndex;

use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;

class HtmlRolesIndex extends AbstractIndex
{
    public function name()
    {
        return "html-roles";
    }

    public function type(): string
    {
        return HtmlRole::class;
    }

    public function componentNames(): array
    {
        $index = $this->index();

        $names = [];
        foreach ($index as $category => $components) {
            $n     = array_keys($components);
            $names = array_merge($names, $n);
        }

        return $names;
    }

    public function filePathPartsFor(string $name): array
    {
        $index = $this->index();
        return $index[$name];
    }

    public function fileName(): string
    {
        $name = $this->name();
        return "{$name}.json";
    }
}
