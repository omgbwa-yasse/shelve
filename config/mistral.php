<?php

/*
|--------------------------------------------------------------------------
| Mistral AI
|--------------------------------------------------------------------------
|
| Variables AI_MISTRAL_* (et non MISTRAL_*) : le package AiBridge lit lui-même
| MISTRAL_API_KEY et enregistre alors son propre provider, qui appelle à tort
| api.openai.com. Ce préfixe garde le provider de l’application.
|
| Renseignez les valeurs dans le fichier .env (jamais ici en dur : ce fichier
| est versionné). Elles sont appliquées en base par :
|
|   php artisan config:clear
|   php artisan db:seed --class="Database\Seeders\AI\MistralDefaultProviderSeeder"
|
| Modèle : `mistral-large-latest` ne répond plus sur certains comptes (appel
| bloqué jusqu'au timeout, sans erreur). `mistral-small-latest` est le défaut.
|
*/

return [
    'api_key' => env('AI_MISTRAL_API_KEY', ''),
    'model' => env('AI_MISTRAL_MODEL', 'mistral-small-latest'),
    'base_url' => env('AI_MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
];
