<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryCode extends Model
{
    protected $fillable = [
        'name', 'code', 'iso', 'flag_url', 'digits', 'format', 'pattern', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Formate un numéro brut selon le format du pays.
     * Ex: "90123456" + format "XX XX XX XX" → "90 12 34 56"
     */
    public function formatNumber(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);
        $format = $this->format;
        $result = '';
        $digitIndex = 0;
        for ($i = 0; $i < strlen($format); $i++) {
            if ($format[$i] === 'X') {
                $result .= $digits[$digitIndex] ?? '';
                $digitIndex++;
            } else {
                $result .= $format[$i];
            }
        }
        return $result;
    }

    /**
     * Vérifie qu'un numéro brut contient exactement le bon nombre de chiffres.
     */
    public function validateRaw(string $raw): bool
    {
        $digits = preg_replace('/\D/', '', $raw);
        return strlen($digits) === $this->digits && preg_match($this->pattern, $digits);
    }
}
