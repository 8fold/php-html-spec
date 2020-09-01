<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\PhpToJson;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAbstract;

use Illuminate\Support\Str;

use Eightfold\HtmlSpecStructured\PhpToJson;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlIndex;

class HtmlContentCategory extends HtmlAbstract
{
    const SUB_FOLDER_NAME = "html-categories";
    const HEADER_TEXT = "Element content categories";

    // static private function stringToSlug(string $string): string
    // {
    //     $replacements = static::replacements();
    //     $givenName = trim($catCell->nodeValue);

    //     $slugAlt = Str::slug($givenName);

    //     $slug = $slugAlt;
    //     if (array_key_exists($slugAlt, $replacements)) {
    //         $slug = $replacements[$slug];
    //     }
    // }

    // static private function replacements(): array
    // {
    //     $parts = PhpToJson::pathPartsToProjectRoot();
    //     $parts[] = "manual";
    //     $parts[] = "replacements.json";
    //     $path = implode("/", $parts);
    //     $json = file_get_contents($path);
    //     $array = json_decode($json, true);

    //     return $array["categories"];
    // }

    static public function storeInitial(): void
    {
        $elements = HtmlIndex::all();
        $replacements = static::replacements();

        $headings = PhpToJson::specSourceDom()->getElementsByTagName("h3");
        for ($i = 0; $i < count($headings); $i++) {
            $node = $headings[$i];
            $isCategoriesHeading = $node->nodeValue === static::HEADER_TEXT;
            if ($isCategoriesHeading) {
                // TODO: DRY with HtmlIndex
                while ($node->tagName !== "table") {
                    $node = $node->nextSibling;
                }
                $table = $node;

                $tBody = $table->getElementsByTagName("tbody");
                $tBody = $tBody[0];
                $rows  = $tBody->getElementsByTagName("tr");

                for ($r = 0; $r < count($rows); $r++) {
                    if ($row = $rows[$r] and $row->tagName === "tr") {
                        $cells = $row->getElementsByTagName("td");

                        $catCell = $cells[0];
                        $slug    = static::stringToSlug($catCell->nodeValue);
                        // $givenName = trim($catCell->nodeValue);

                        // $slugAlt = Str::slug($givenName);

                        // $slug = $slugAlt;
                        // if (array_key_exists($slugAlt, $replacements)) {
                        //     $slug = $replacements[$slug];
                        // }

                        $elemCell = $cells[1];
                        $elems = $elemCell->nodeValue;
                        $elems = explode(";", $elems);
                        $elems = array_map("trim", $elems);
                        $elems = array_intersect($elements->elementNames(), $elems);
                        $elems = array_values($elems);

                        $exceptionCell = $cells[2];
                        $exceptions = trim($exceptionCell->nodeValue);
                        if ($exceptions === "—") {
                            $exceptions = [];

                        } else {
                            $exceptions = explode(";", $exceptions);
                            $exceptions = array_map("trim", $exceptions);
                            $except = [];
                            foreach ($exceptions as $content) {
                                list($key, $c) = explode(" ", $content, 2);
                                $except[$key] = str_replace(["(", ")"], "", $c);
                            }
                            $exceptions = $except;
                        }

                        $category = [$slug =>
                            [
                                "name"       => $givenName,
                                "slug"       => $slug,
                                "slugAlt"    => $slugAlt,
                                "elements"   => $elems,
                                "exceptions" => $exceptions
                            ]
                        ];

                        $json = json_encode($category, JSON_PRETTY_PRINT);

                        $parts = static::folderPathParts();
                        $parts[] = $slug .".json";

                        $path = implode("/", $parts);
                        file_put_contents($path, $json);
                    }
                }
            }
        }
    }
}
