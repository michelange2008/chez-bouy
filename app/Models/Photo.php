<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Photo extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['published' => 'boolean'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PhotoTranslation::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(PhotoLocation::class, 'photo_location')->withPivot('position');
    }

    public function translatedTitle(): string
    {
        return $this->translations->firstWhere('locale', app()->getLocale())?->title
            ?? $this->translations->firstWhere('locale', 'fr')?->title
            ?? $this->filename;
    }
}
