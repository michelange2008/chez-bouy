<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annuaire extends Model
{
    use HasFactory;

    public function formattedTelephone(): ?string
    {
        if ($this->telephone === null) {
            return null;
        }

        $telephone = preg_replace('/[\s.\-]+/', '', $this->telephone);

        if (preg_match('/^0[0-9]{9}$/', $telephone)) {
            return implode(' ', str_split($telephone, 2));
        }

        if (preg_match('/^(\+33|0033)(\(0\))?([1-9])([0-9]{8})$/', $telephone, $matches)) {
            return $matches[1].' '.($matches[2] !== '' ? '(0)' : '').$matches[3].' '.implode(' ', str_split($matches[4], 2));
        }

        return $this->telephone;
    }
}
