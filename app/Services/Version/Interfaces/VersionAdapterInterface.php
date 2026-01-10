<?php

namespace App\Services\Version\Interfaces;

use App\Services\Version\DTOs\FileDTO;
use App\Services\Version\DTOs\VersionDTO;

interface VersionAdapterInterface
{
    /**
     * @param array<int, FileDTO> $files
     */
    public function adapt(array $files): VersionDTO;
}

