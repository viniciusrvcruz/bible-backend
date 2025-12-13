<?php

namespace App\Services\Version\Factories;

use App\Services\Version\Importers\ThiagoBodrukImporter;
use App\Services\Version\Interfaces\VersionImporterInterface;
use RuntimeException;

class VersionImporterFactory
{
    private static array $importers = [
        [
            'name' => 'Thiago Bodruk Importer',
            'class' => ThiagoBodrukImporter::class,
        ],
    ];

    public static function getAvailableImporters(): array
    {
        return collect(self::$importers)
            ->map(fn($importer) => $importer['name'])
            ->toArray();
    }

    public static function make(string $importerName): VersionImporterInterface
    {
        $importer = collect(self::$importers)->firstWhere('name', $importerName);

        if(!$importer) {
            throw new RuntimeException("Importer type '{$importerName}' not found");
        }

        return new $importer['class']();
    }
}
