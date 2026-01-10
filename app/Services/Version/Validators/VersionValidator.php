<?php

namespace App\Services\Version\Validators;

use App\Services\Version\DTOs\VersionDTO;
use App\Services\Version\DTOs\BookDTO;
use App\Services\Version\DTOs\ChapterDTO;
use App\Services\Version\DTOs\VerseDTO;
use App\Exceptions\Version\VersionImportException;

class VersionValidator
{
    public function validate(VersionDTO $dto): void
    {
        foreach ($dto->books as $index => $book) {
            // Validate that each book is an instance of BookDTO
            if (!$book instanceof BookDTO) {
                throw new VersionImportException('invalid_book_type', "Book at index {$index} is not an instance of BookDTO");
            }

            if ($book->chapters->isEmpty()) {
                throw new VersionImportException('missing_chapters', "Book '{$book->name}' is missing chapters");
            }

            foreach ($book->chapters as $chapter) {
                // Validate that each chapter is an instance of ChapterDTO
                if (!$chapter instanceof ChapterDTO) {
                    throw new VersionImportException('invalid_chapter_type', "Chapter in book '{$book->name}' is not an instance of ChapterDTO");
                }

                if ($chapter->verses->isEmpty()) {
                    throw new VersionImportException('missing_verses', "Chapter {$chapter->number} in book '{$book->name}' is missing verses");
                }

                foreach ($chapter->verses as $verse) {
                    // Validate that each verse is an instance of VerseDTO
                    if (!$verse instanceof VerseDTO) {
                        throw new VersionImportException('invalid_verse_type', "Verse in chapter {$chapter->number} of book '{$book->name}' is not an instance of VerseDTO");
                    }

                    if (empty(trim($verse->text))) {
                        throw new VersionImportException('empty_verse', "Verse {$verse->number} in chapter {$chapter->number} of book '{$book->name}' has empty text");
                    }
                }
            }
        }
    }
}
