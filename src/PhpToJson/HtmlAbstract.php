<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson;

abstract class HtmlAbstract
{
    const SUB_FOLDER_NAME = "default";
    const TABLE_ID = "default";

    static public function tableBody()
    {
        $table = PhpToJson::specSourceDom()->getElementById(static::TABLE_ID);
        return $table->getElementsByTagName("tbody")[0];
    }

    static public function storeInitial(): void
    {
        $tableBody = static::tableBody();

        $rows = $tableBody->getElementsByTagName("tr");
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $name = trim($row->getElementsByTagName("th")[0]->nodeValue);

            $cells = $row->getElementsByTagName("td");
            $build = [];
            for ($j = 0; $j < count($cells); $j++) {
                $cell = $cells[$j];
                $build[] = strip_tags($cell->nodeValue);
            }
            $cells = array_map("trim", $build);

            $content = static::objectFromTableCells($name, $cells);

            $pathParts = static::folderPathParts();
            $pathParts[] = "{$name}.json";
            $filePath = implode("/", $pathParts);

            $json = json_encode($content, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $json);

            static::updateGlobalElements();
        }
    }

    static public function updateGlobalElements()
    {
        $objects = static::all();
        $objects = array_filter($objects, function($v) {
            return $v->elements[0] === "HTMLelements";
        });

        $elements = HtmlIndex::all()->elementNames();
        foreach ($objects as $object) {
            $name = $object->name;

            $pathParts = static::folderPathParts();
            $pathParts[] = "{$name}.json";
            $filePath = implode("/", $pathParts);

            unset($object->elements);
            $object->elements = $elements;

            $json = json_encode($object, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $json);
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

    static public function all()
    {
        $contents = scandir(static::folderPath());
        $files = array_filter($contents, function($v) {
            $fileParts = explode(".", $v, 2);
            return (isset($fileParts[1]) and
                $extension = $fileParts[1] === "json");
        });

        return array_map(function($v) {
            $attrName = explode(".", $v, 2);
            $attrName = $attrName[0];

            $parts = static::folderPathParts();
            $parts[] = $v;
            $path = implode("/", $parts);
            $json = file_get_contents($path);
            $object = json_decode($json);

            return $object->{$attrName};

        }, $files);
    }

    static public function folderPath(): string
    {
        $pathParts = static::folderPathParts();
        $folderPath = implode("/", $pathParts);
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        return $folderPath;
    }

    static public function folderPathParts(): array
    {
        $parts = PhpToJson::pathPartsToJson();
        $parts[] = static::SUB_FOLDER_NAME;
        $folderPath = implode("/", $parts);
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        return $parts;
    }
}
