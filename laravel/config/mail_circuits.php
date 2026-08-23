<?php

return [
    'default_due_days' => 3,
    'financial_threshold' => 5000000,

    /*
     * Les modèles restent volontairement lisibles : une étape possède un niveau
     * (stage), un valideur fonctionnel et, éventuellement, une condition. Des
     * étapes de même niveau sont ouvertes en parallèle.
     */
    'templates' => [
        'direct' => [
            'name' => 'Circuit direct autorisé',
            'category' => 'Courant',
            'description' => 'Information simple sans engagement : autorisation immédiate de transmission.',
            'steps' => [],
        ],
        'n1' => [
            'name' => 'Validation simple N+1',
            'category' => 'Hiérarchique',
            'description' => 'Un seul visa du supérieur direct avant transmission.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Visa du N+1', 'stage' => 1, 'validator' => 'n1'],
            ],
        ],
        'hierarchical' => [
            'name' => 'Validation hiérarchique N+1 puis N+2',
            'category' => 'Hiérarchique',
            'description' => 'Remontée séquentielle sur deux niveaux de responsabilité.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Visa du N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'n2', 'label' => 'Arbitrage du N+2', 'stage' => 2, 'validator' => 'n2'],
            ],
        ],
        'dg_decision' => [
            'name' => 'Position officielle avec décision DG',
            'category' => 'Hiérarchique',
            'description' => 'N+1, N+2 puis validation finale de la Direction générale.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Visa du N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'n2', 'label' => 'Visa du N+2', 'stage' => 2, 'validator' => 'n2'],
                ['key' => 'dg', 'label' => 'Décision / signature DG', 'stage' => 3, 'validator' => 'dg'],
            ],
        ],
        'invoice' => [
            'name' => 'Facture fournisseur',
            'category' => 'Finance',
            'description' => 'Service fait, contrôle Finance, puis DG lorsque le seuil ou la règle interne l’impose.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Confirmation du service fait (N+1)', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'finance', 'label' => 'Contrôle Finance : budget, pièces et conformité', 'stage' => 2, 'validator' => 'finance'],
                ['key' => 'dg', 'label' => 'Autorisation DG selon le seuil', 'stage' => 3, 'validator' => 'dg', 'condition' => 'financial_threshold_or_dg'],
            ],
        ],
        'payment' => [
            'name' => 'Demande de paiement',
            'category' => 'Finance',
            'description' => 'Visa hiérarchique, contrôle Finance et décision DG conditionnelle.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Visa du N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'finance', 'label' => 'Visa Finance', 'stage' => 2, 'validator' => 'finance'],
                ['key' => 'dg', 'label' => 'Autorisation DG selon le seuil', 'stage' => 3, 'validator' => 'dg', 'condition' => 'financial_threshold_or_dg'],
            ],
        ],
        'purchase' => [
            'name' => 'Devis, commande ou engagement',
            'category' => 'Finance',
            'description' => 'N+1, Achats, Finance, puis DG selon le seuil.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Validation du besoin par le N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'purchase', 'label' => 'Contrôle Achats', 'stage' => 2, 'validator' => 'purchase'],
                ['key' => 'finance', 'label' => 'Visa Finance', 'stage' => 3, 'validator' => 'finance'],
                ['key' => 'dg', 'label' => 'Autorisation DG selon le seuil', 'stage' => 4, 'validator' => 'dg', 'condition' => 'financial_threshold_or_dg'],
            ],
        ],
        'contract' => [
            'name' => 'Contrat ou avenant',
            'category' => 'Engagement',
            'description' => 'N+1, avis Finance et Juridique en parallèle, puis signature DG.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Validation du besoin par le N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'finance', 'label' => 'Avis Finance', 'stage' => 2, 'validator' => 'finance', 'parallel' => true],
                ['key' => 'legal', 'label' => 'Avis Juridique', 'stage' => 2, 'validator' => 'legal', 'parallel' => true],
                ['key' => 'dg', 'label' => 'Signature DG', 'stage' => 3, 'validator' => 'dg'],
            ],
        ],
        'it_contract' => [
            'name' => 'Contrat informatique ou données',
            'category' => 'Engagement',
            'description' => 'N+1, avis Finance/Juridique/DSI en parallèle, puis DG.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Validation du besoin par le N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'finance', 'label' => 'Avis Finance', 'stage' => 2, 'validator' => 'finance', 'parallel' => true],
                ['key' => 'legal', 'label' => 'Avis Juridique / données personnelles', 'stage' => 2, 'validator' => 'legal', 'parallel' => true],
                ['key' => 'dsi', 'label' => 'Avis DSI / sécurité', 'stage' => 2, 'validator' => 'dsi', 'parallel' => true],
                ['key' => 'dg', 'label' => 'Signature DG', 'stage' => 3, 'validator' => 'dg'],
            ],
        ],
        'cyber_incident' => [
            'name' => 'Incident informatique ou cyber',
            'category' => 'Urgence',
            'description' => 'Action DSI immédiate, régularisation Juridique/service, puis information ou décision DG.',
            'urgent' => true,
            'steps' => [
                ['key' => 'dsi', 'label' => 'Confinement / prise en charge DSI', 'stage' => 1, 'validator' => 'dsi', 'due_days' => 0],
                ['key' => 'service_manager', 'label' => 'Information du responsable métier', 'stage' => 2, 'validator' => 'service_manager', 'parallel' => true],
                ['key' => 'legal', 'label' => 'Avis Juridique / protection des données', 'stage' => 2, 'validator' => 'legal', 'parallel' => true],
                ['key' => 'dg', 'label' => 'Information et décision DG', 'stage' => 3, 'validator' => 'dg'],
            ],
        ],
        'external_request' => [
            'name' => 'Demande externe ou organe de contrôle',
            'category' => 'Externe',
            'description' => 'N+1, avis spécialisés déclenchés selon le contenu, puis DG si la réponse est officielle.',
            'steps' => [
                ['key' => 'n1', 'label' => 'Visa du N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'finance', 'label' => 'Avis Finance', 'stage' => 2, 'validator' => 'finance', 'parallel' => true, 'condition' => 'finance_required'],
                ['key' => 'legal', 'label' => 'Avis Juridique', 'stage' => 2, 'validator' => 'legal', 'parallel' => true, 'condition' => 'legal_required'],
                ['key' => 'dsi', 'label' => 'Avis DSI', 'stage' => 2, 'validator' => 'dsi', 'parallel' => true, 'condition' => 'dsi_required'],
                ['key' => 'dg', 'label' => 'Validation de la réponse officielle', 'stage' => 3, 'validator' => 'dg', 'condition' => 'official_or_dg'],
            ],
        ],
        'sensitive' => [
            'name' => 'Courrier sensible ou confidentiel',
            'category' => 'Sensible',
            'description' => 'Accès restreint aux participants, N+1, Juridique puis DG.',
            'restricted' => true,
            'steps' => [
                ['key' => 'n1', 'label' => 'Visa du N+1', 'stage' => 1, 'validator' => 'n1'],
                ['key' => 'legal', 'label' => 'Contrôle Juridique / confidentialité', 'stage' => 2, 'validator' => 'legal'],
                ['key' => 'dg', 'label' => 'Décision DG', 'stage' => 3, 'validator' => 'dg'],
            ],
        ],
        'public_communication' => [
            'name' => 'Communication publique ou presse',
            'category' => 'Communication',
            'description' => 'Contrôle Juridique puis validation finale DG.',
            'steps' => [
                ['key' => 'legal', 'label' => 'Contrôle Juridique / DPD', 'stage' => 1, 'validator' => 'legal'],
                ['key' => 'dg', 'label' => 'Validation DG', 'stage' => 2, 'validator' => 'dg'],
            ],
        ],
        'custom' => [
            'name' => 'Circuit personnalisé',
            'category' => 'Personnalisé',
            'description' => 'Jusqu’à quatre valideurs choisis, en séquence ou en parallèle.',
            'steps' => [],
        ],
    ],
];
