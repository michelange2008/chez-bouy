<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhotoLocation extends Model
{
    use HasFactory;

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'photo_location')->withPivot('position');
    }
}
