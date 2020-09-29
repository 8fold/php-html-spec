<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read\Interfaces;

use Eightfold\HtmlSpec\Read\Interfaces\Fileable;
use Eightfold\HtmlSpec\Read\Interfaces\HtmlComponent;

interface IndexReader extends Fileable
{
    static public function init(): IndexReader;

    public function componentNames(): array;

    public function componentNamed(string $name): HtmlComponent;

    public function hasComponentNamed(string $name): bool;

    public function index(): array;

    public function type(): string;

    public function filePathPartsFor(string $name): array;
}
