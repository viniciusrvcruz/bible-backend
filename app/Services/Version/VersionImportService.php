<?php

namespace App\Services\Version;

use App\Models\Version;
use App\Services\Version\Factories\VersionImporterFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class VersionImportService
{
    public function import(string $filePath, string $importerName, string $versionName, string $copyright): Version
    {
        $content = Storage::get($filePath);
        $importer = VersionImporterFactory::make($importerName);

        $importer->validate($content);

        return DB::transaction(function () use ($versionName, $copyright, $content, $importer) {
            $version = Version::create([
                'name' => $versionName,
                'copyright' => $copyright,
            ]);

            $importer->import($content, $version->id);

            $this->validateImport($version);

            return $version;
        });
    }

    private function validateImport(Version $version): void
    {
        $chaptersCount = $version->chapters()->count();
        $versesCount = $version->chapters()->withCount('verses')->get()->sum('verses_count');

        if ($chaptersCount !== 1189) {
            throw new RuntimeException("Expected 1,189 chapters but got {$chaptersCount}");
        }

        if ($versesCount < 31100 || $versesCount > 31110) {
            throw new RuntimeException("Expected verses between 31,100 and 31,110 but got {$versesCount}");
        }
    }
}
