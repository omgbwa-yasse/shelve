<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
   use HasFactory;

   protected array $fillable = [
       'name',
       'description',
       'date_start',
       'date_end',
       'date_exact',
       'date_type',
       'frequence_type',
       'frequence_value',
       'prompt_id',
       'user_id',
       'is_public',
       'is_trained'
   ];

   protected array $casts = [
       'date_start' => 'date',
       'date_end' => 'date',
       'date_exact' => 'date',
       'is_public' => 'boolean',
       'is_trained' => 'boolean',
       'frequence_value' => 'integer'
   ];

   protected array $enums = [
       'date_type' => ['start_only', 'exact', 'range'],
       'frequence_type' => ['day', 'heure', 'min']
   ];



   public function prompt(): BelongsTo
   {
       return $this->belongsTo(Prompt::class, 'prompt_id');
   }

   public function user(): BelongsTo
   {
       return $this->belongsTo(User::class, 'user_id');
   }


   // Constantes pour les types de dates
   public const DATE_TYPE_START_ONLY = 'start_only';
   public const DATE_TYPE_EXACT = 'exact';
   public const DATE_TYPE_RANGE = 'range';

   public const DATE_TYPES = [
       self::DATE_TYPE_START_ONLY,
       self::DATE_TYPE_EXACT,
       self::DATE_TYPE_RANGE
   ];

   // Constantes pour les types de fréquence
   public const FREQUENCY_TYPE_DAY = 'day';
   public const FREQUENCY_TYPE_HOUR = 'heure';
   public const FREQUENCY_TYPE_MINUTE = 'min';

   public const FREQUENCY_TYPES = [
       self::FREQUENCY_TYPE_DAY,
       self::FREQUENCY_TYPE_HOUR,
       self::FREQUENCY_TYPE_MINUTE
   ];

}
