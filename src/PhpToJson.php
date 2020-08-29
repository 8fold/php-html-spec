<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured;

use \DomDocument;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElement;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;

class PhpToJson
{
    static public function compile(): void
    {
        HtmlAttribute::storeHtmlAttributes();
        HtmlElement::storeHtmlElements();
        HtmlElement::updateElementAttributes();
        HtmlElement::updateElementDetails();
    }

    static public function updateHtmlElementIndex(HtmlElement $element)
    {
        if (! file_exists(HtmlElement::pathToHtmlElementIndex())) {
            if (! file_exists(static::pathToHtml())) {
                mkdir(static::pathToHtml(), 0755, true);
            }
            file_put_contents(HtmlElement::pathToHtmlElementIndex(), "{}");
        }

        $path = HtmlElement::pathToHtmlElementIndex();

        $json = file_get_contents($path);
        $index = json_decode($json);
        $index->{$element->name()} = array_values($element->filePathPartsRelative());

        $json = json_encode($index, JSON_PRETTY_PRINT);
        file_put_contents($path, $json);
    }

    static public function specSourceDom(): DomDocument
    {
        $content = PhpToJson::curlContent("https://raw.githubusercontent.com/whatwg/html/master/source");
        $dom = new \DomDocument();
        @$dom->loadHtml($content);
        return $dom;
    }

    static public function curlContent(string $url = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    static public function pathToHtml()
    {
        $parts = static::pathPartsToHtml();
        return implode("/", $parts);
    }

    static private function pathPartsToHtml()
    {
        $parts = static::pathPartsToJson();
        $parts[] = "html";
        return $parts;
    }

    static public function pathPartsToJson()
    {
        $dir = __DIR__;
        $dir = explode("/", $dir);
        array_pop($dir);
        $dir[] = "json";

        return $dir;
    }
}
