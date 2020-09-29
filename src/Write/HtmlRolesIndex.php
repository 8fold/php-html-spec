<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write;

use Eightfold\HtmlSpec\Read\HtmlRolesIndex as HtmlRolesIndexReader;

use Illuminate\Support\Str;

use Eightfold\HtmlSpec\Compiler;

use Eightfold\HtmlSpec\Write\HtmlRole;

class HtmlRolesIndex extends HtmlRolesIndexReader
{
    static public function storeInitial(): void
    {
        $index = static::init();

        $dom = Compiler::specAriaDom();

        $table = $dom->getElementById("aria-table");
        $tBody = $table->getElementsByTagName("tbody")[0];
        $rows  = $tBody->getElementsByTagName("tr");

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName("td");

            $roles = $cells[0];
            $role  = trim($roles->nodeValue);
            $name  = str_replace("`", "", $role);

            $roleDescription = $cells[1];
            $roleDescription = trim($roleDescription->nodeValue);
            $description     = strip_tags($roleDescription);

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
                $trimmed = trim($requiredProps->nodeValue);
                $requiredProps = [str_replace("`", "", $trimmed)];

            }

            $supportedProps = $cells[3];
            $isList = count($supportedProps->getElementsByTagName("ul")) > 0;
            if ($isList) {
                $list = $supportedProps->getElementsByTagName("ul")[0];
                $supportedProps = $list->getElementsByTagName("li");

            } else {
                $trimmed = trim($supportedProps->nodeValue);
                $supportedProps = [str_replace("`", "", $trimmed)];

            }

            foreach ($supportedProps as $listItem) {
                $base = $listItem;
                if (! is_string($base)) {
                    $base = trim($base->nodeValue);
                }
                $base = str_replace("`", "", $base);

                $separated = explode(" ", $base, 2);
                if (count($separated) > 1 and $separated[1] === "(state)") {
                    $isProperty = false;

                }
            }

            $children = $cells[4];
            $children = trim($children->nodeValue);
            $content  = static::stringToSlug($children);

            $restrictions = $cells[5];
            $restrictions = str_replace(
                "\n             ",
                "",
                trim($restrictions->nodeValue)
            );

            $template = HtmlRole::TEMPLATE;

            $template["name"]                    = $name;
            $template["is-property"]             = $isProperty;
            $template["description"]             = $description;
            $template["required-properties"]     = $requiredProps;
            $template["supported-properties"]    = $supportedProps;
            $template["kind-of-content"]         = $content;
            $template["descendent-restrictions"] = $restrictions;

            $object = (object) $template;

            $role = HtmlRole::fromObject($object);

            $role->save();

            $index->addComponent($role);
        }
        $index->save();
    }

    static private function stringToSlug(string $string): string
    {
        $replacements = static::replacements();
        $string = trim($string);

        $slugAlt = Str::slug($string);

        $slug = $slugAlt;
        if (array_key_exists($slugAlt, $replacements)) {
            $slug = $replacements[$slug];
        }
        return $slug;
    }

    static private function replacements(): array
    {
        $parts = Compiler::pathPartsToProjectRoot();
        $parts[] = "local";
        $parts[] = "replacements.json";
        $path = implode("/", $parts);
        $json = file_get_contents($path);
        $array = json_decode($json, true);

        return $array["categories"];
    }

    public function addComponent(HtmlRole $role): HtmlRolesIndex
    {
        $name      = $role->name();
        $fileParts = $role->filePathPartsRelative();
        $fileParts = array_values($fileParts);
        $category  = $fileParts[1];

        $this->index[$name] = $fileParts;

        return $this;
    }

    public function save(): HtmlRolesIndex
    {
        $index = $this->index();
        ksort($index, SORT_NATURAL);

        $json = json_encode($index, JSON_PRETTY_PRINT);
        $path = $this->filePath();

        file_put_contents($path, $json);

        return $this;
    }
}
