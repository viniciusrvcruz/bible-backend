<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VersionImportRequest;
use App\Services\Version\DTOs\VersionImportDTO;
use App\Services\Version\VersionImportService;
use Illuminate\Http\Response;

class VersionImportController extends Controller
{
    public function __invoke(VersionImportRequest $request, VersionImportService $service)
    {
        $dto = new VersionImportDTO(
            content: $request->file('file')->getContent(),
            importerName: $request->input('importer'),
            versionName: $request->input('name'),
            copyright: $request->input('copyright', ''),
            fileExtension: $request->file('file')->getExtension(),
        );

        $version = $service->import($dto);

        return response()->json([
            'id' => $version->id,
            'name' => $version->name,
            'chapters_count' => $version->chapters()->count(),
            'verses_count' => $version->chapters()->withCount('verses')->get()->sum('verses_count'),
        ], Response::HTTP_CREATED);
    }
}
