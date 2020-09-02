<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read;

use Illuminate\Support\Str;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\AbstractIndex;

use Eightfold\HtmlSpecStructured\Read\Interfaces\IndexReader;

use Eightfold\HtmlSpecStructured\Read\Interfaces\Fileable;
use Eightfold\HtmlSpecStructured\Read\Traits\FileableImp;

use Eightfold\HtmlSpecStructured\Read\HtmlContentCategory;

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
