<?php
// declare(strict_types=1);

namespace Eightfold\HtmlSpecStructured\Read\Interfaces;

interface HtmlComponent
{
    public function component(): object;

    public function name(): string;

    public function elements(): array;

    public function categories(): array;

    public function folderPathParts(): array;

    public function filePathParts(): array;

    public function filePath(): string;

    public function filePathPartsRelative(): array;

    public function fileName(): string;
}
