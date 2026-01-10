<?php

use App\Services\Version\Validators\VersionValidator;
use App\Services\Version\DTOs\{VersionDTO, BookDTO, ChapterDTO, VerseDTO};
use App\Enums\BookAbbreviationEnum;
use App\Exceptions\Version\VersionImportException;

describe('VersionValidator', function () {
    beforeEach(function () {
        $this->validator = new VersionValidator();
    });

    describe('validate', function () {
        it('validates correct structure', function () {
            $books = collect([
                new BookDTO(
                    'Genesis',
                    BookAbbreviationEnum::GEN,
                    collect([
                        new ChapterDTO(1, collect([new VerseDTO(1, 'Text')]))
                    ])
                )
            ]);

            $dto = new VersionDTO($books);

            expect(fn() => $this->validator->validate($dto))->not->toThrow(Exception::class);
        });

        it('throws exception for book without chapters', function () {
            $books = collect([
                new BookDTO(
                    'Genesis',
                    BookAbbreviationEnum::GEN,
                    collect()
                )
            ]);

            $this->validator->validate(new VersionDTO($books));
        })->throws(VersionImportException::class, 'is missing chapters');

        it('throws exception for chapter without verses', function () {
            $books = collect([
                new BookDTO(
                    'Genesis',
                    BookAbbreviationEnum::GEN,
                    collect([new ChapterDTO(1, collect())])
                )
            ]);

            $this->validator->validate(new VersionDTO($books));
        })->throws(VersionImportException::class, 'is missing verses');

        it('throws exception for empty verse text', function () {
            $books = collect([
                new BookDTO(
                    'Genesis',
                    BookAbbreviationEnum::GEN,
                    collect([
                        new ChapterDTO(1, collect([new VerseDTO(1, '   ')]))
                    ])
                )
            ]);

            $this->validator->validate(new VersionDTO($books));
        })->throws(VersionImportException::class, 'has empty text');

        it('throws exception when book is not an instance of BookDTO', function () {
            $books = collect([
                (object) [
                    'name' => 'Genesis',
                    'chapters' => collect()
                ]
            ]);

            $dto = new VersionDTO($books);

            $this->validator->validate($dto);
        })->throws(VersionImportException::class, 'is not an instance of BookDTO');

        it('throws exception when chapter is not an instance of ChapterDTO', function () {
            $books = collect([
                new BookDTO(
                    'Genesis',
                    BookAbbreviationEnum::GEN,
                    collect([
                        (object) ['number' => 1, 'verses' => collect()]
                    ])
                )
            ]);

            $dto = new VersionDTO($books);

            $this->validator->validate($dto);
        })->throws(VersionImportException::class, 'is not an instance of ChapterDTO');

        it('throws exception when verse is not an instance of VerseDTO', function () {
            $books = collect([
                new BookDTO(
                    'Genesis',
                    BookAbbreviationEnum::GEN,
                    collect([
                        new ChapterDTO(1, collect([
                            (object) ['number' => 1, 'text' => 'Text']
                        ]))
                    ])
                )
            ]);

            $dto = new VersionDTO($books);

            $this->validator->validate($dto);
        })->throws(VersionImportException::class, 'is not an instance of VerseDTO');
    });
});
