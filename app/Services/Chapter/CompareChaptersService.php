<?php

namespace App\Services\Chapter;

use App\Enums\BookNameEnum;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CompareChaptersService
{
    public function execute(int $number, BookNameEnum $bookName, string $versions, ?string $verses = null): Collection
    {
        $versionIds = $this->parseVersions($versions);
        $verseNumbers = $verses ? $this->parseVerses($verses) : null;

        $chapters = Chapter::whereIn('version_id', $versionIds)
            ->where('number', $number)
            ->whereHas('book', fn(Builder $query) => $query->where('name', $bookName->value))
            ->with(['verses', 'version'])
            ->get();

        return $chapters->map(function (Chapter $chapter) use ($verseNumbers) {
            $verses = $chapter->verses;

            if ($verseNumbers !== null) {
                $verses = $verses->filter(fn($verse) => in_array($verse->number, $verseNumbers))
                    ->sortBy('number')
                    ->values();
            } else {
                $verses = $verses->sortBy('number')->values();
            }

            return [
                'version_id' => $chapter->version_id,
                'version_name' => $chapter->version->name,
                'chapter_number' => $chapter->number,
                'verses' => $verses->map(fn($verse) => [
                    'number' => $verse->number,
                    'text' => $verse->text,
                ])->values(),
            ];
        });
    }

    private function parseVersions(string $versions): array
    {
        return array_map('intval', array_filter(explode(',', $versions)));
    }

    private function parseVerses(string $verses): array
    {
        $numbers = [];
        $parts = explode(',', $verses);

        foreach ($parts as $part) {
            $part = trim($part);
            
            if (str_contains($part, '-')) {
                [$start, $end] = explode('-', $part);
                $numbers = array_merge($numbers, range((int)$start, (int)$end));
            } else {
                $numbers[] = (int)$part;
            }
        }

        return array_unique($numbers);
    }
}

