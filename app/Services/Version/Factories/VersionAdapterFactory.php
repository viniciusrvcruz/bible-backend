<?php

namespace App\Services\Version\Factories;

use App\Services\Version\Interfaces\VersionAdapterInterface;
use App\Services\Version\Adapters\JsonThiagoBodrukAdapter;
use App\Exceptions\Version\VersionImportException;

class VersionAdapterFactory
{
    private static array $adapters = [
        'json_thiago_bodruk' => JsonThiagoBodrukAdapter::class,
    ];

    public static function make(string $format): VersionAdapterInterface
    {
        $adapterClass = self::$adapters[$format] ?? null;

        if (!$adapterClass) {
            throw new VersionImportException('adapter_not_found', "Adapter for format '{$format}' not found");
        }

        return app($adapterClass);
    }

    public static function getAvailableFormats(): array
    {
        return array_keys(self::$adapters);
    }
}

