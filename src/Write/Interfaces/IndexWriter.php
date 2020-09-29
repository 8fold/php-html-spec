<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write\Interfaces;

use Eightfold\HtmlSpec\Read\Interfaces\Fileable;

interface IndexWriter
{
    public function components(): array;

    public function addComponent(Fileable $component): IndexWriter;

    public function save(): IndexWriter;
}
