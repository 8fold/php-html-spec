<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpec\Write\Interfaces;

interface ComponentWriter
{
    public function save(): ComponentWriter;
}
