<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activation de la limitation de débit
    |--------------------------------------------------------------------------
    |
    | Activée par défaut, et elle doit le rester en production.
    |
    | Elle n'est désactivée que pour la suite de conformité de l'API
    | (contracts/conformance) : cette suite éprouve l'authentification et dépasse
    | mécaniquement le quota `auth,5,60` — cinq tentatives par heure ne permettent
    | pas de vérifier le mot de passe erroné, le compte inconnu et la validation
    | des entrées dans une même exécution.
    |
    | Conséquence à connaître : quand ce réglage est à false, le comportement 429
    | n'est plus vérifiable. Les tests qui portent sur les quotas eux-mêmes doivent
    | donc s'exécuter séparément, avec la limitation active.
    |
    */

    'enabled' => env('RATE_LIMIT_ENABLED', true),

];
