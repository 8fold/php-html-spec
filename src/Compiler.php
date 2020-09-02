<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured;

use \DomDocument;

use Eightfold\HtmlSpecStructured\Write\HtmlIndex; // TODO: move to Write
use Eightfold\HtmlSpecStructured\Write\HtmlRolesIndex;
use Eightfold\HtmlSpecStructured\Write\HtmlContentCategoryIndex;
use Eightfold\HtmlSpecStructured\Write\HtmlAttributeIndex;

// TODO: rename -> Compiler
class Compiler
{
    static public function compile(): void
    {
        HtmlIndex::storeInitial(); // Initial HTML elements set.
        HtmlRolesIndex::storeInitial();

        HtmlContentCategoryIndex::storeInitial();
        HtmlAttributeIndex::storeInitial(); // Add attributes

        HtmlIndex::storeDetails(); // Update elements with details
        // HtmlIndex::storeAriaRoles(); // "roles" => ["default" => "", "optional" => []]
    }

    static public function htmlElementList(): array
    {
        $json = static::curlContent("https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json");
        $elementList = json_decode($json);
        return $elementList;
    }

    static public function specSourceDom(): DomDocument
    {
        return static::domFromCurlContent("https://raw.githubusercontent.com/whatwg/html/master/source");
    }

    static public function specAriaDom(): DomDocument
    {
        return static::domFromCurlContent("https://raw.githubusercontent.com/w3c/html-aria/gh-pages/index.html");
    }

    static public function specAriaPropertiesDom(): DomDocument
    {
        $parts   = static::pathPartsToProjectRoot();
        $parts[] = "local";
        $parts[] = "aria.html";
        $path    = implode("/", $parts);

        $content = file_get_contents($path);
        $dom = new \DomDocument();
        @$dom->loadHtml($content);
        return $dom;
    }

    static private function domFromCurlContent(string $path): DomDocument
    {
        $content = static::curlContent($path);
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

    static public function replacementsFor(string $key): array
    {
        $parts = Compiler::pathPartsToProjectRoot();
        $parts[] = "local";
        $parts[] = "replacements.json";
        $path = implode("/", $parts);
        $json = file_get_contents($path);
        $array = json_decode($json, true);

        return $array[$key];
    }
}
