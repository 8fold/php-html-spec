<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\AbstractComponent;

class HtmlAttribute extends AbstractComponent
{
    const TEMPLATE = [
        "name"        => "",
        "elements"    => [],
        "roles"       => [],
        "description" => "",
        "value"       => [],
        "categories"  => []
    ];

    public function elements(int $index = PHP_INT_MAX)
    {
        $component = (array) $this->component();
        if ($index === PHP_INT_MAX) {
            return $component["elements"];
        }
        return $component["elements"][$index];
    }

    public function value(): string
    {
        $component = (array) $this->component();
        return $component["value"];
    }

    public function categories(): array
    {
        if (count($this->component()->categories) === 0) {
            $name = $this->name();
            if (substr($name, 0, 2) === "on") {
                $this->component->categories = ["events"];

            } elseif (substr($name, 0, 4) === "aria") {
                $this->component->categories = ["aria"];

            } elseif (substr($name, 0, 4) === "data") {
                $this->component->categories = ["data"];

            } elseif ($this->elements(0) === "HTML elements") {
                $this->component->categories = ["global"];

            } elseif ($this->value() === "Boolean attribute") {
                $this->component->categories = ["boolean"];

            } else {
                $this->component->categories = ["other"];

            }
        }
        return $this->component()->categories;
    }

    public function isEvent(): bool
    {
        $categories = $this->categories();
        return $categories[0] === "events";
    }

    public function isAria(): bool
    {
        $categories = $this->categories();
        return $categories[0] === "aria";
    }

    public function isData(): bool
    {
        $categories = $this->categories();
        return $categories[0] === "data";
    }

    public function isGlobal(): bool
    {
        $categories = $this->categories();
        return $categories[0] === "global";
    }

    public function isBoolean(): bool
    {
        $categories = $this->categories();
        return $categories[0] === "boolean";
    }

    public function isOther(): bool
    {
        $categories = $this->categories();
        return $categories[0] === "other";
    }

    public function subFolder(): string
    {
        $categories = $this->categories();
        return $categories[0];

    }

    public function folderPathParts(): array
    {
        $parts   = Compiler::pathPartsToJson();
        $parts[] = "html-attributes";
        $parts[] = $this->subFolder();

        $path = implode("/", $parts);
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $parts;
    }
}
