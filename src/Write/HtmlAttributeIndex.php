<?php
// declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use \ArrayAccess;
use \Iterator;
use \DomNode;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Read\HtmlAttributeIndex as HtmlAttributeIndexReader;
use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;

use Eightfold\HtmlSpec\Write\HtmlAttribute;
use Eightfold\HtmlSpec\Write\Interfaces\IndexWriter;
use Eightfold\HtmlSpec\Write\Traits\TableProcessing;

class HtmlAttributeIndex extends HtmlAttributeIndexReader
{
    static public function storeInitial()
    {
        static::storeAttributesTable("attributes-1");
        static::storeAttributesTable("ix-event-handlers");
        static::storeAttributesAria();
    }

    static public function rowsForTableWithId(string $id = "")
    {
        $table     = Compiler::specSourceDom()->getElementById($id);
        $tableBody = $table->getElementsByTagName("tbody")[0];
        return $tableBody->getElementsByTagName("tr");
    }

    static public function storeAttributesTable(string $id)
    {
        $index = static::init();

        $rows = static::rowsForTableWithId($id);
        $attributes = [];
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName("th");
            $name = $cells[0];

            $cells       = $row->getElementsByTagName("td");

            $elements = $cells[0];
            $description = $cells[1];
            $value = $cells[2];

            $template                = HtmlAttribute::TEMPLATE;
            $template["name"]        = trim($name->nodeValue);
            $template["elements"]    = array_map("trim", explode(";", $elements->nodeValue));
            $template["description"] = trim($description->nodeValue);
            $template["value"]       = trim($value->nodeValue);

            $object = (object) $template;

            $attribute = HtmlAttribute::fromObject($object);
            $attribute->save();

            $index->addComponent($attribute);

        }

        // Add role - not stored in table
        $template                = HtmlAttribute::TEMPLATE;
        $template["name"]        = "role";
        $template["elements"]    = ["HTML elements"];
        $template["description"] = "Bridge into ARIA attributes";
        $template["value"]       = "Text: known or custom ARIA role.";

        $object = (object) $template;

        $attribute = HtmlAttribute::fromObject($object);
        $attribute->save();

        $index->addComponent($attribute);

        $index->save();
    }

    static public function storeAttributesAria()
    {
        $index = static::init();
        $dom = Compiler::specAriaDom();
        $domDetails = Compiler::specAriaPropertiesDom();
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

        foreach ($dictionary as $attrName => $definition) {
            $object = (object) $definition;
            $attr = HtmlAttribute::fromObject($object);
            $attr->save();
            $index->addComponent($attr);
        }

        $index->save();
    }

    public function addComponent(HtmlComponent $component): HtmlAttributeIndex
    {
        $index = $this->index();
        $categories = $component->categories();
        foreach ($categories as $category) {
            $index[$category][$component->name()] = array_values($component->filePathPartsRelative());

        }
        $this->index = $index;
        return $this;
    }

    public function save(): HtmlAttributeIndex
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
