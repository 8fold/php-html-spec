<?php
declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Write\Interfaces;

interface ComponentWriter
{
    public function save(): ComponentWriter;
}
