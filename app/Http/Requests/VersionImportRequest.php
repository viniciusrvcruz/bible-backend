<?php

namespace App\Http\Requests;

use App\Services\Version\Factories\VersionImporterFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VersionImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'importer' => ['required', 'string', Rule::in(VersionImporterFactory::getAvailableImporters())],
            'name' => ['required', 'string', 'unique:versions,name'],
            'copyright' => ['nullable', 'string'],
        ];
    }
}
