<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoTranslation extends Model
{
    public $timestamps = false;

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}
