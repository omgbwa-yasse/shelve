<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Relation avec les records
     */
    public function records()
    {
        return $this->belongsToMany(Record::class, 'record_keyword')
                    ->withTimestamps();
    }

    /**
     * Relation avec les slip_records
     */
    public function slipRecords()
    {
        return $this->belongsToMany(SlipRecord::class, 'slip_record_keyword')
                    ->withTimestamps();
    }

    /**
     * Méthode statique pour créer ou récupérer un mot-clé
     */
    public static function findOrCreate($name)
    {
        $name = trim($name);
        if (empty($name)) {
            return null;
        }

        return static::firstOrCreate(
            ['name' => $name]
        );
    }

    /**
     * Méthode pour traiter une chaîne de mots-clés séparés par des points-virgules
     */
    public static function processKeywordsString($keywordsString)
    {
        if (empty($keywordsString)) {
            return collect();
        }

        $keywords = collect(explode(';', $keywordsString))
            ->map(function ($keyword) {
                return trim($keyword);
            })
            ->filter(function ($keyword) {
                return !empty($keyword);
            })
            ->unique()
            ->map(function ($keyword) {
                return self::findOrCreate($keyword);
            })
            ->filter(); // Enlève les valeurs null

        return $keywords;
    }
}
