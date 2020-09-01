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
        static::storeAttributesAria();
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
            $attribute->save();

            $index->addComponent($attribute);
        }
        $index->save();
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
        $index = static::all();
        $dom = PhpToJson::specAriaDom();
        $domDetails = PhpToJson::specAriaPropertiesDom();
        $ids = [
            "attrs_widgets"       => "aria-widgets",
            "attrs_liveregions"   => "aria-live-regions",
            "attrs_dragdrop"      => "aria-drag-and-drop",
            "attrs_relationships" => "aria-relationships"
        ];

        $dictionary = [];
        $categories = [];
        foreach ($ids as $id => $category) {
            $list      = $domDetails->getElementById($id)->getElementsByTagName("ul")[0];
            $listItems = $list->getElementsByTagName("li");

            for ($i = 0; $i < count($listItems); $i++) {
                $node = $listItems[$i];

                $name = $node->nodeValue;

                $categories[$name] = [$category];
            }
        }

        $table = $dom->getElementById("aria-table");
        $tBody = $table->getElementsByTagName("tbody")[0];
        $rows  = $tBody->getElementsByTagName("tr");

        for ($i = 0; $i < count($rows); $i++) {
            $node = $rows[$i];
            $cells = $node->getElementsByTagName("td");

            $roles = $cells[0];
            $role  = trim($roles->nodeValue);
            $role  = str_replace("`", "", $role);

            $roleDescription = $cells[1];
            $roleDescription = trim($roleDescription->nodeValue);
            $roleDescription = strip_tags($roleDescription);

            $requiredProps = $cells[2];
            $isList = count($requiredProps->getElementsByTagName("ul")) > 0;
            if ($isList) {
                $list = $requiredProps->getElementsByTagName("ul")[0];
                $requiredProps = $list->getElementsByTagName("li");
                $r = [];
                foreach ($requiredProps as $n) {
                    $r[] = str_replace("`", "", trim($n->nodeValue));
                }
                $requiredProps = $r;

            } else {
                $requiredProps = [trim($requiredProps->nodeValue)];

            }

            $children = $cells[4];
            $children = trim($children->nodeValue);
            $children = static::stringToSlug($children);

            $supportedProperties = $cells[3];
            $isList = count($supportedProperties->getElementsByTagName("ul")) > 0;
            if ($isList) {
                $list = $supportedProperties->getElementsByTagName("ul")[0];
                $supportedProperties = $list->getElementsByTagName("li");

            } else {
                $supportedProperties = [trim($supportedProperties->nodeValue)];

            }

            foreach ($supportedProperties as $listItem) {
                $attrName = str_replace("`", "", trim($listItem->nodeValue));

                if (strlen($attrName) > 0) {
                    $property = true;
                    if (strpos($attrName, " (state)") > 0) {
                        $property = false;

                    }
                    list($n) = explode(" ", $attrName, 2);
                    $attrName = $n;

                    $dictionary[$attrName]["name"] = $attrName;
                    $dictionary[$attrName]["property"] = $property;

                    if (isset($categories[$attrName])) {
                        $dictionary[$attrName]["categories"] = $categories[$attrName];

                    } else {
                        $dictionary[$attrName]["categories"] = ["aria-other"];

                    }
                    $dictionary[$attrName]["roles"][] = ($role === "any") ? "global" : $role;
                    $dictionary[$attrName]["role-description"] = $roleDescription;
                    $dictionary[$attrName]["required-properties"] = $requiredProps;
                    $dictionary[$attrName]["children"] = $children;
                }
            }
        }

        foreach ($dictionary as $attrName => $definition) {
            $object = (object) $definition;
            $attr = HtmlAttribute::fromObject($object);
            $attr->save();
            $index->addComponent($attr);
        }

        $index->save();
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
        die(var_dump($this->index()));
        foreach ($this->components() as $componentName) {
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
