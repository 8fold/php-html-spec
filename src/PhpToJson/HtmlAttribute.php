<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson;

class HtmlAttribute
{
    static public function storeHtmlAttributes()
    {
        $attributesTable = PhpToJson::specSourceDom()
            ->getElementById("attributes-1");

        $tableBody = $attributesTable->childNodes[4];
        $rows = $tableBody->childNodes;

        $rowElements = [];
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]->nodeName !== "#text") {
                $rowElements[] = $rows[$i];
            }
        }

        foreach ($rowElements as $element) {
            $cells = $element->childNodes;

            $attribute = [];
            $attributeName = "";
            for ($i = 0; $i < count($cells) ; $i++) {
                $cell = $cells[$i];
                if ($cell->nodeName !== "#text") {
                    $cellContent = trim($cell->nodeValue);
                    switch ($i) {
                        case 1:
                            $attributeName = $cellContent;
                            $attribute[$cellContent]["name"] = $cellContent;
                            break;

                        case 2:
                            $attribute[$attributeName]["elements"] = explode("; ", $cellContent);
                            break;

                        case 3:
                            $attribute[$attributeName]["description"] = $cellContent;
                            break;

                        default:
                            $attribute[$attributeName]["misc"] = $cellContent;
                            break;
                    }
                }
            }

            $parts = static::folderPathPartsToHtmlAttributes();
            $parts[] = "{$attributeName}.json";
            $filePath = implode("/", $parts);

            $json = json_encode($attribute, JSON_PRETTY_PRINT);
            file_put_contents($filePath, $json);
        }
    }

    static public function folderPathToHtmlAttributes(): string
    {
        $pathParts = static::folderPathPartsToHtmlAttributes();
        $folderPath = implode("/", $pathParts);
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        return $folderPath;
    }

    static public function folderPathPartsToHtmlAttributes(): array
    {
        $parts = PhpToJson::pathPartsToJson();
        $parts[] = "html-attributes";
        $folderPath = implode("/", $parts);
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        return $parts;
    }
}
