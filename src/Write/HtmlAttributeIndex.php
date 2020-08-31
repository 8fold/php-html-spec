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
        static::storeAttributesTable();
        // static::storeEventHandlingTable();
        // static::storeAriaTable();

    }

    static public function storeAttributesTable()
    {
        $index = static::all();
        $rows = static::rowsForTableWithId("attributes-1");
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
        if (count($dictionary["elements"]) === 1 and
            ($dictionary["elements"][0] == "HTML elements" or $dictionary["elements"][0] == "HTMLelements")
        ) {
            // TODO: breaking DRY
            $pathParts[] = "html-attributes-global";

        } elseif ($dictionary["misc"] === "Boolean attribute") {
            $pathParts[] = "html-attributes-boolean";

        } else {
            $pathParts[] = "html-attributes";

        }

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

    public function addComponent(HtmlComponent $component): IndexWriter
    {
        $categories = $component->categories();
        foreach ($categories as $category) {
            $this->index[$category][$component->name()] = $component->filePathPartsRelative();

        }
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
        $json = json_encode($this->index(), JSON_PRETTY_PRINT);
        file_put_contents($this->filePath(), $json);

        return $this;
    }
}
