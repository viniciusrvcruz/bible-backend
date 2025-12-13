<?php

namespace App\Console\Commands;

use App\Models\Version;
use App\Services\Version\Factories\VersionImporterFactory;
use App\Services\Version\VersionImportService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

class VersionImportCommand extends Command
{
    protected $signature = 'version:import {file}';
    protected $description = 'Import a Bible version from file';

    public function handle(VersionImportService $service): int
    {
        $filename = $this->argument('file');

        $filePath = "versions/{$filename}";

        if (!Storage::exists($filePath)) {
            $this->error("File not found: storage/app/private/{$filePath}");
            return self::FAILURE;
        }

        $this->info("Bible Version Import");

        $importers = VersionImporterFactory::getAvailableImporters();
        $importerName = select(
            label: 'What is your favorite programming language?',
            options: $importers,
        );

        $versionName = $this->ask('What is the version name?', 'new_version');

        $existsVersion = Version::where('name', $versionName)->exists();

        if($existsVersion) {
            $this->error("Version {$versionName} already exists");
            return self::FAILURE;
        }

        $copyright = $this->ask('Copyright information (optional)', '');

        if (!confirm('Do you want to proceed with the import?')) {
            $this->warn('Import cancelled');
            return self::SUCCESS;
        }

        try {
            $version = $service->import($filePath, $importerName, $versionName, $copyright);

            $this->info("✓ Successfully imported: {$version->name}");
            $this->line("  Chapters: {$version->chapters()->count()}");
            $this->line("  Verses: {$version->chapters()->withCount('verses')->get()->sum('verses_count')}");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("✗ Import failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
