<?php
// declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write;

use \ArrayAccess;
use \Iterator;
use \DomNode;

use Eightfold\HtmlSpecStructured\PhpToJson;
use Eightfold\HtmlSpecStructured\Read\HtmlAttributeIndex as HtmlAttributeIndexReader;

use Eightfold\HtmlSpecStructured\Write\HtmlAttribute;

use Eightfold\HtmlSpecStructured\Write\Interfaces\IndexWriter;

use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;

use Eightfold\HtmlSpecStructured\Write\Traits\TableProcessing;


// use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class HtmlAttributeIndex extends HtmlAttributeIndexReader implements IndexWriter
{
    use TableProcessing;

    static public function storeInitial()
    {
        static::storeAttributesTable("attributes-1");
        static::storeAttributesTable("ix-event-handlers");
        // static::storeAttributesAria();
    }

    static public function storeAttributesTable(string $id)
    {
        $index = static::all();
        $rows = static::rowsForTableWithId($id);
        for ($i = 0; $i < count($rows); $i++) {
            $row       = $rows[$i];
            $pathParts = PhpToJson::pathPartsToJson();

            $indexPathParts   = $pathParts;
            $indexPathParts[] = "html-attributes.json";

            $attribute = static::processRowForPathParts($row, $pathParts);

            $index->addComponent($attribute);
        }
        $index->saveComponents()->save();
    }

    static public function processRowForPathParts(
        DomNode $row,
        array $pathParts
    ): HtmlAttribute
    {
        $name = trim($row->getElementsByTagName("th")[0]->nodeValue);

        $cells = $row->getElementsByTagName("td");
        $build = [];
        for ($j = 0; $j < count($cells); $j++) {
            $cell = $cells[$j];
            $build[] = strip_tags($cell->nodeValue);
        }
        $cells = array_map("trim", $build);

        $object = static::objectFromTableCells($name, $cells);

        $dictionary = $object->{$name};
        $n = $dictionary["name"];
        $isEvent  = substr($n, 0, 2) === "on";
        $isGlobal = (count($dictionary["elements"]) === 1 and
            ($dictionary["elements"][0] === "HTML elements" or $dictionary["elements"][0] === "HTMLelements")
        );

        if ($isEvent) {
            if ($isGlobal) {
                $pathParts[] = "html-attributes-events-global";
                $dictionary["categories"] = ["global-events"];

            } else {
                $pathParts[] = "html-attributes-events";
                $dictionary["categories"] = ["other-events"];
            }

        } elseif ($isGlobal) {
            // TODO: breaking DRY
            $pathParts[] = "html-attributes-global";
            $dictionary["categories"] = ["global"];

        } elseif ($dictionary["misc"] === "Boolean attribute") {
            $pathParts[] = "html-attributes-boolean";
            $dictionary["categories"] = ["boolean"];

        } else {
            $pathParts[] = "html-attributes";
            $dictionary["categories"] = ["other"];

        }
        $object = (object) $dictionary;

        $folderPath = implode("/", $pathParts);
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $pathParts[] = "{$name}.json";

        $filePath = implode("/", $pathParts);

        $json = json_encode($object, JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);

        return HtmlAttribute::fromObject($object);
    }

    // static public function storeAttributesEvents()
    // {
    //     $index = static::all();
    //     $rows = static::rowsForTableWithId("ix-event-handlers");
    //     for ($i = 0; $i < count($rows); $i++) {
    //         $row       = $rows[$i];
    //         $pathParts = PhpToJson::pathPartsToJson();

    //         $indexPathParts   = $pathParts;
    //         $indexPathParts[] = "html-attributes.json";

    //         $attribute = static::processRowForPathParts($row, $pathParts);

    //         $index->addComponent($attribute);
    //     }
    //     $index->saveComponents()->save();
    // }

    static public function storeAttributesAria()
    {
        var_dump(__FILE__);
        var_dump(__LINE__);
/**
{
    "name": {name},
    "roles": [
        #{name}
    ],
    "description": {#inde_stat_prop definitions},
    "misc": "Text*",
    "categories": [
        #global_states, #attrs_widgets, #attrs_liveregions, #attrs_dragdrop, #attrs_relationships
    ]
}
*/
    }

    public function addComponent(HtmlComponent $component): IndexWriter
    {
        $index = $this->index();
        $categories = $component->categories();
        foreach ($categories as $category) {
            $index[$category][$component->name()] = $component->filePathPartsRelative();

        }
        $this->index = $index;
        return $this;
    }

    public function saveComponents(): IndexWriter
    {
        foreach ($this->components() as $component) {
            if (is_a($component, HtmlComponent::class)) {
                $component->save();

            }
        }
        return $this;
    }

    public function save(): IndexWriter
    {
        $index = $this->index();
        ksort($index, SORT_NATURAL);
        foreach ($index as $category => $components) {
            ksort($components);
            $index[$category] = $components;
        }
        $json = json_encode($index, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath(), $json);

        return $this;
    }
}
