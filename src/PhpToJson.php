<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured;

use \DomDocument;

use Eightfold\HtmlSpecStructured\PhpToJson\HtmlIndex;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlElements;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlAttribute;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlEventHandler;
use Eightfold\HtmlSpecStructured\PhpToJson\HtmlContentCategory;

class PhpToJson
{
    static public function compile(): void
    {
        HtmlIndex::storeInitial(); // Establish base for reference.

        HtmlContentCategory::storeInitial();
        HtmlAttribute::storeInitial();
        HtmlEventHandler::storeInitial();

        HtmlIndex::storeDetails(); // This takes forever - never ends ??
        // HtmlIndex::storeAttributes(); // This takes forever - never ends ??
        // HtmlElement::updateEventHandlers();
        // HtmlElement::updateAriaAttributes();
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

    static public function pathPartsToJson()
    {
        $dir = static::pathPartsToProjectRoot();
        $dir[] = "json";

        return $dir;
    }

    static public function pathPartsToProjectRoot()
    {
        $dir = __DIR__;
        $dir = explode("/", $dir);
        array_pop($dir);

        return $dir;
    }
}
