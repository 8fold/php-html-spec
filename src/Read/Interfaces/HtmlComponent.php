<?php
// declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read\Interfaces;

use Eightfold\HtmlSpec\Read\Interfaces\Fileable;

interface HtmlComponent extends Fileable
{
    static public function fromPath(string $path): HtmlComponent;

    static public function fromObject(object $component): HtmlComponent;

    public function __construct(object $component);
}
