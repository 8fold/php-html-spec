<?php
// declare(strict_types=1);

namespace Eightfold\HtmlSpec\Read\Interfaces;

interface Fileable
{
    public function fileName(): string;

    public function filePath(): string;

    public function filePathParts(): array;

    public function folderPathParts(): array;
}
