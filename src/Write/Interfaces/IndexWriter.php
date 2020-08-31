<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write\Interfaces;

use Eightfold\HtmlSpecStructured\Read\Interfaces\HtmlComponent;

interface IndexWriter
{
    public function components(): array;

    public function addComponent(HtmlComponent $component): IndexWriter;

    public function saveComponents(): IndexWriter;

    public function save(): IndexWriter;
}
