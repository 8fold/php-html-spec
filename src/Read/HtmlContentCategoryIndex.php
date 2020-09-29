<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read;

use Illuminate\Support\Str;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\AbstractIndex;

use Eightfold\HtmlSpec\Read\Interfaces\IndexReader;

use Eightfold\HtmlSpec\Read\Interfaces\Fileable;
use Eightfold\HtmlSpec\Read\Traits\FileableImp;

use Eightfold\HtmlSpec\Read\HtmlContentCategory;

class HtmlContentCategoryIndex extends AbstractIndex
{
    static public function indexKeyFromNiceName(string $niceName): string
    {
        $niceName = trim($niceName);
        $niceName = strip_tags($niceName);
        $slug     = Str::slug($niceName);

        $replacements = Compiler::replacementsFor("categories");
        $indexKey     = $replacements[$slug];
        return $indexKey;
    }

    public function type(): string
    {
        return HtmlContentCategory::class;
    }

    public function fileName(): string
    {
        return "html-content-categories.json";
    }

    public function hasComponentNamed(string $name): bool
    {
        return in_array($name, $this->componentNames());
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
        if (isset($index[$name])) {
            return $index[$name];
        }
        return [];
    }
}
