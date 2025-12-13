<?php

namespace App\Services\Version\Importers;

use App\Enums\BookNameEnum;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;
use App\Services\Version\Interfaces\VersionImporterInterface;
use InvalidArgumentException;

class ThiagoBodrukImporter implements VersionImporterInterface
{
    public function validate(string $content): void
    {
        $data = json_decode($content, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        if(!is_array($data)) {
            throw new InvalidArgumentException('JSON must be an array of books');
        }

        if(count($data) !== 66) {
            throw new InvalidArgumentException('Expected 66 books but got ' . count($data));
        }

        foreach ($data as $index => $book) {
            $chapters = $book['chapters'] ?? null;
            $bookName = BookNameEnum::cases()[$index] ?? null;

            if (empty($chapters) || !is_array($chapters)) {
                throw new InvalidArgumentException("Book '{$bookName->value}' is missing 'chapters'");
            }

            foreach ($chapters as $chapterIndex => $verses) {
                $chapterNumber = $chapterIndex + 1;

                if(!is_array($verses)) {
                    throw new InvalidArgumentException("Chapter {$chapterNumber} in the book '{$bookName->value}' must be an array of verses.");
                }

                foreach ($verses as $verseIndex => $verse) {
                    $verseNumber = $verseIndex + 1;

                    if(!is_string($verse)) {
                        throw new InvalidArgumentException("Verse {$verseNumber} in chapter {$chapterNumber} of the book '{$bookName->value}' must be a string.");
                    }
                }
            }
        }
    }

    public function import(string $content, int $versionId): void
    {
        $data = json_decode($content, true);
        $books = Book::all();

        foreach ($data as $index => $bookData) {
            $book = $books->firstWhere('name', BookNameEnum::cases()[$index]->value);

            foreach ($bookData['chapters'] as $chapterIndex => $chapterVerses) {
                $chapterNumber = $chapterIndex + 1;

                $chapter = Chapter::create([
                    'number' => $chapterNumber,
                    'book_id' => $book->id,
                    'version_id' => $versionId,
                ]);

                $verses = collect($chapterVerses)->map(fn($verse, $verseIndex) => [
                    'chapter_id' => $chapter->id,
                    'number' => $verseIndex + 1,
                    'text' => $verse,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                Verse::insert($verses);
            }
        }
    }
}
