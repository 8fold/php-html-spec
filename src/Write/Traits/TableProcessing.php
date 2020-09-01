<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write\Traits;

use Illuminate\Support\Str;

use Eightfold\HtmlSpecStructured\PhpToJson;

trait TableProcessing
{
    static public function tableBodyWithId(string $id, bool $useSpec = true)
    {
        $table = PhpToJson::specSourceDom()->getElementById($id);
        return $table->getElementsByTagName("tbody")[0];
    }

    static public function rowsForTableWithId(string $id = "")
    {
        $tableBody = static::tableBodyWithId($id);
        return $tableBody->getElementsByTagName("tr");
    }

    static public function processRowForPathParts(DomNode $row, array $pathParts): void
    {
        $name = trim($row->getElementsByTagName("th")[0]->nodeValue);

        $cells = $row->getElementsByTagName("td");
        $build = [];
        for ($j = 0; $j < count($cells); $j++) {
            $cell = $cells[$j];
            $build[] = strip_tags($cell->nodeValue);
        }
        $cells = array_map("trim", $build);

        $content = static::objectFromTableCells($name, $cells);

        $pathParts[] = "{$name}.json";
        $filePath = implode("/", $pathParts);

        $json = json_encode($content, JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);
    }

    static public function storeInitial(): void
    {
        $rows = rowsForTableWithId(static::TABLE_ID);
        for ($i = 0; $i < count($rows); $i++) {
            $row       = $rows[$i];
            $pathParts = static::folderPathParts();

            static::processRowForPathParts($row, $pathParts);
            static::updateGlobalElements();
        }
    }

    static public function objectFromTableCells(string $th, array $cells): object
    {
        return (object) [$th =>
            [
                "name"        => $th,
                "elements"    => explode(";", str_replace(
                    [" ", "\t", "\n", "\r", "\0", "\x0B"],
                    "",
                    $cells[0]
                )),
                "description" => $cells[1],
                "misc"        => $cells[2]
            ]
        ];
    }

    static private function stringToSlug(string $string): string
    {
        $replacements = static::replacements();
        $string = trim($string);

        $slugAlt = Str::slug($string);

        $slug = $slugAlt;
        if (array_key_exists($slugAlt, $replacements)) {
            $slug = $replacements[$slug];
        }
        return $slug;
    }

    static private function replacements(): array
    {
        $parts = PhpToJson::pathPartsToProjectRoot();
        $parts[] = "local";
        $parts[] = "replacements.json";
        $path = implode("/", $parts);
        $json = file_get_contents($path);
        $array = json_decode($json, true);

        return $array["categories"];
    }
}
