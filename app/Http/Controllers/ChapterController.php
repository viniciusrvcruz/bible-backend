<?php

namespace App\Http\Controllers;

use App\Actions\Chapter\GetChapterAction;
use App\Enums\BookAbbreviationEnum;
use App\Http\Resources\ChapterResponseResource;
use App\Models\Version;

class ChapterController extends Controller
{
    public function show(Version $version, BookAbbreviationEnum $abbreviation, int $number)
    {
        $chapter = app(GetChapterAction::class)->execute(
            number: $number,
            abbreviation: $abbreviation,
            version: $version
        );

        return new ChapterResponseResource($chapter);
    }
}
