<?php

namespace App\Services\Version\Interfaces;

interface VersionImporterInterface
{
    public function validate(string $content): void;
    public function import(string $content, int $versionId): void;
}
