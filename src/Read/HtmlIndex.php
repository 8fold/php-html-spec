<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\AbstractIndex;

use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;

class HtmlIndex extends AbstractIndex
{
    public function name()
    {
        return "html";
    }

    public function type(): string
    {
        return HtmlElement::class;
    }

    public function hasComponentNamed(string $name): bool
    {
        return in_array($name, $this->componentNames());
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
        foreach ($index as $category => $elements) {
            if (isset($elements[$name])) {
                return $elements[$name];
            }
        }
        var_dump($name);
    }

    public function fileName(): string
    {
        $name = $this->name();
        return "{$name}.json";
    }
}
