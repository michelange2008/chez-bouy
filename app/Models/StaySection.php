<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StaySection extends Model
{
    use HasFactory;

    public const PAGES = ['arrivee', 'installation', 'appartement', 'depart', 'chamonix'];

    public const EDITABLE_PAGES = ['home', ...self::PAGES];

    public function pageRoute(): string
    {
        return $this->page === 'home' ? 'home' : 'stay.'.$this->page;
    }

    protected function casts(): array
    {
        return ['published' => 'boolean', 'translations' => 'array'];
    }

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class)->withPivot('position', 'locale')->orderByPivot('position')->orderBy('photos.id');
    }

    public function photosForLocale(string $locale): BelongsToMany
    {
        return $this->photos()->withPivotValue('locale', $locale);
    }

    /** @return array{title: ?string, body: ?string} */
    public function translatedContent(): array
    {
        $translation = $this->translations[app()->getLocale()] ?? [];
        if (empty($translation['body'])) {
            $translation = $this->translations['fr'] ?? [];
        }

        return ['title' => $translation['title'] ?? null, 'body' => $translation['body'] ?? null];
    }
}
