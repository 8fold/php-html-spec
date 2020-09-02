<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write;

use Eightfold\HtmlSpecStructured\Read\HtmlContentCategoryIndex as HtmlContentCategoryIndexReader;

use Eightfold\HtmlSpecStructured\Compiler;

use Eightfold\HtmlSpecStructured\Read\HtmlIndex;
use Eightfold\HtmlSpecStructured\Read\Interfaces\Fileable;

use Eightfold\HtmlSpecStructured\Write\Interfaces\IndexWriter;
use Eightfold\HtmlSpecStructured\Write\HtmlContentCategory;

class HtmlContentCategoryIndex extends HtmlContentCategoryIndexReader
{
    const HEADER_TEXT = "Element content categories";

    static public function storeInitial(): void
    {
        $index = HtmlContentCategoryIndex::init();

        $replacements = Compiler::replacementsFor("categories");

        $headings = Compiler::specSourceDom()->getElementsByTagName("h3");
        $rows = [];
        foreach ($headings as $heading) {
            $isCategoriesHeading = $heading->nodeValue === static::HEADER_TEXT;
            $table = $heading->nextSibling;
            if ($isCategoriesHeading) {
                // TODO: DRY with HtmlIndex
                while ($table->tagName !== "table") {
                    $table = $table->nextSibling;
                }
                $tBody = $table->getElementsByTagName("tbody");
                $tBody = $tBody[0];
                $rows  = $tBody->getElementsByTagName("tr");
                break;
            }
        }

        foreach($rows as $row) {
            $cells = $row->getElementsByTagName("td");

            $category = $cells[0];
            $category = static::indexKeyFromNiceName($category->nodeValue);

            $storedElements = HtmlIndex::init()->componentNames();
            $elements = $cells[1];
            $elements = explode(";", $elements->nodeValue);
            $elements = array_map("trim", $elements);
            $elements = array_intersect($storedElements, $elements);
            $elements = array_values($elements);

            $exceptions = $cells[2];
            $exceptions = trim($exceptions->nodeValue);
            $exceptions = ($exceptions === "—") ? "none" : $exceptions;

            $dictionary               = HtmlContentCategory::TEMPLATE;
            $dictionary["name"]       = $category;
            $dictionary["elements"]   = $elements;
            $dictionary["exceptions"] = $exceptions;

            $object = (object) $dictionary;

            $category = HtmlContentCategory::fromObject($object);
            $category->save();

            $index->addComponent($category);
        }
        $index->save();
    }

    public function type(): string
    {
        return HtmlContentCategory::class;
    }

    public function addComponent(HtmlContentCategory $category): HtmlContentCategoryIndex
    {
        $name      = $category->name();
        $fileParts = array_values($category->filePathPartsRelative());

        $this->index[$name] = $fileParts;

        return $this;
    }

    public function save(): HtmlContentCategoryIndex
    {
        $index = $this->index();
        ksort($index, SORT_NATURAL);

        $json = json_encode($index, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath(), $json);

        return $this;
    }
}
