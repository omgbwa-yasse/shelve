<?php

/**
 * Phase 1 — Étape 1.0.1 : inventaire exhaustif des endpoints.
 *
 * Lit contracts/inventory/routes.json (php artisan route:list --json)
 * et produit contracts/inventory/endpoints.csv, classé par domaine D01–D16.
 *
 * Colonnes : method, uri, name, controller, action, middleware, domaine, type, statut
 *
 * Usage : php scripts/build-endpoint-inventory.php
 */

$root = dirname(__DIR__);
$in = $root . '/contracts/inventory/routes.json';
$out = $root . '/contracts/inventory/endpoints.csv';
$summary = $root . '/contracts/inventory/endpoints-summary.md';

if (!is_file($in)) {
    fwrite(STDERR, "Fichier absent : $in\nLancer d'abord : php artisan route:list --json > contracts/inventory/routes.json\n");
    exit(1);
}

$routes = json_decode(file_get_contents($in), true, 512, JSON_THROW_ON_ERROR);

/**
 * Affectation contrôleur → domaine (cf. evolution/README.md §0.3).
 * Les motifs sont testés dans l'ordre : le premier qui matche gagne.
 * Les motifs les plus spécifiques doivent donc précéder les plus généraux.
 */
$domains = [
    // D15 — Portail public / OPAC (en premier : préempte Public*/OPAC* sur tous les autres motifs)
    'D15' => [
        '#^App\\\\Http\\\\Controllers\\\\OPAC\\\\#',
        '#^App\\\\Http\\\\Controllers\\\\Api\\\\Public#',
        '#^App\\\\Http\\\\Controllers\\\\Public#',
    ],

    // D14 — IA
    'D14' => [
        '#Controllers\\\\Api\\\\Ai#i',
        '#Controllers\\\\Api\\\\AISettings#i',
        '#Controllers\\\\Ai(Search|Skill|Template|Resource)#',
        '#Controllers\\\\(Ollama|Prompt)#',
    ],

    // D02 — Records (notices)
    'D02' => [
        '#Controllers\\\\Settings\\\\(RecordType|RecordDigital|DocumentTypeMetadata|FolderTypeMetadata|MetadataDefinition)#',
        '#Controllers\\\\Api\\\\(RecordDigital|Metadata|RecordPeriodic|Attachment|RecordSearch)#',
        '#Controllers\\\\Record(Attachment|Author|Child|Container|DragDrop|Reactivation|DigitalTransfer|Status|Support|Document)?Controller#',
    ],

    // D04 — Versements / bordereaux
    'D04' => [
        '#Controllers\\\\(Slip|slipRecordAttachment|Accession)#',
        '#Controllers\\\\Api\\\\ContainerSearch#',
    ],

    // D08 — Thésaurus
    'D08' => ['#Controllers\\\\(Thesaurus|Api\\\\ThesaurusImport)#'],

    // D06 — Courrier
    'D06' => ['#Controllers\\\\(Mail|Batch)#'],

    // D11 — Dolly
    'D11' => ['#Controllers\\\\Dolly#'],

    // D10 — Recherche
    'D10' => ['#Controllers\\\\Search#'],

    // D12 — Collaboration
    'D12' => ['#Controllers\\\\(Workplace|Chat|Task)#'],

    // D13 — Workflow
    'D13' => ['#Controllers\\\\Workflow#'],

    // D05 — Communications & réservations
    'D05' => ['#Controllers\\\\(Communication|communicationRecord|Reservation|activityCommunicability)#'],

    // D07 — Cycle de vie / rétention
    'D07' => ['#Controllers\\\\(Retention|LifeCycle|Declassement)#'],

    // D09 — Organisation & sécurité
    'D09' => [
        '#^App\\\\Http\\\\Controllers\\\\Auth\\\\#',
        '#Controllers\\\\(Organisation|User|Role|RolePermission|UserRole|UserOrganisationRole|Agent)#',
    ],

    // D03 — Localisation physique
    'D03' => ['#Controllers\\\\(Building|Floor|Room|Shelf|Container|Localisation)#'],

    // D16 — Exploitation
    'D16' => [
        '#Controllers\\\\(Backup|Log|Monitoring|SystemUpdate|RateLimit|NewFeature|Tools|Report|PDF|Barcode|SEDAExport|Dashboard|Home|Phantom)#',
    ],

    // D01 — Référentiels (filet : tout le reste des contrôleurs applicatifs)
    'D01' => [
        '#Controllers\\\\(Activity|Language|Sort|Author|Keyword|Law|Communicability|Setting|SettingCategory|Settings\\\\ReferenceList|External)#',
    ],
];

$domainLabels = [
    'D01' => 'Référentiels',
    'D02' => 'Records (notices)',
    'D03' => 'Localisation physique',
    'D04' => 'Versements / bordereaux',
    'D05' => 'Communications & réservations',
    'D06' => 'Courrier (Mail)',
    'D07' => 'Cycle de vie / rétention',
    'D08' => 'Thésaurus (SKOS)',
    'D09' => 'Organisation & sécurité',
    'D10' => 'Recherche',
    'D11' => 'Dolly (paniers)',
    'D12' => 'Collaboration',
    'D13' => 'Workflow',
    'D14' => 'IA',
    'D15' => 'Portail public / OPAC',
    'D16' => 'Exploitation',
    '—' => 'Hors périmètre (framework)',
    '???' => 'NON CLASSÉ — à traiter',
];

/** Routes techniques du framework : hors périmètre de la migration. */
$outOfScope = [
    '#^Laravel\\\\Dusk#',
    '#^Laravel\\\\Sanctum#',
    '#^Spatie\\\\LaravelIgnition#',
    '#^L5Swagger#',
    '#^Illuminate\\\\Routing\\\\ViewController#',
    '#^Closure$#',
];

function classifyDomain(string $action, array $domains, array $outOfScope): string
{
    foreach ($outOfScope as $re) {
        if (preg_match($re, $action)) {
            return '—';
        }
    }
    foreach ($domains as $code => $patterns) {
        foreach ($patterns as $re) {
            if (preg_match($re, $action)) {
                return $code;
            }
        }
    }
    return '???';
}

/**
 * Type d'endpoint, déduit du nom de méthode et de l'URI.
 * crud   : index/show/store/update/destroy — portage direct en API
 * vue    : create/edit — rend un formulaire, NON porté (mais alimente un endpoint /options)
 * export : produit un fichier (pdf, excel, seda, ead, csv…)
 * upload : reçoit un fichier
 * action : tout le reste — action métier, à recenser explicitement
 */
function classifyType(string $method, string $uri, string $action): string
{
    if ($action === '' || $action === 'Closure') {
        return 'action';
    }

    $m = strtolower($action);

    if (in_array($m, ['index', 'show', 'store', 'update', 'destroy'], true)) {
        return 'crud';
    }
    if (in_array($m, ['create', 'edit'], true)) {
        return 'vue';
    }
    if (preg_match('#^(export|download|print|pdf|excel|csv|generate.*(pdf|excel|report)|render)#i', $m)
        || preg_match('#/(export|download|print|pdf)#i', $uri)) {
        return 'export';
    }
    if (preg_match('#^(upload|attach|import|store.*attachment)#i', $m)) {
        return 'upload';
    }

    return 'action';
}

$rows = [];
$stats = [];
$unclassified = [];

foreach ($routes as $r) {
    $actionFull = $r['action'] ?? '';
    $controller = $actionFull;
    $method = '';

    if (str_contains($actionFull, '@')) {
        [$controller, $method] = explode('@', $actionFull, 2);
    }

    $uri = '/' . ltrim($r['uri'] ?? '', '/');
    $httpMethods = $r['method'] ?? '';
    // route:list ajoute HEAD systématiquement : bruit inutile pour l'inventaire
    $httpMethods = implode('|', array_diff(explode('|', $httpMethods), ['HEAD']));

    $domain = classifyDomain($controller, $domains, $outOfScope);
    $type = $domain === '—' ? 'framework' : classifyType($httpMethods, $uri, $method);

    // Statut initial : ce qui doit être porté en API v1
    $statut = match (true) {
        $domain === '—' => 'hors-périmètre',
        $type === 'vue' => 'abandonné',      // formulaire Blade : remplacé par un écran Next
        default => 'à-porter',
    };

    $rows[] = [
        $httpMethods,
        $uri,
        $r['name'] ?? '',
        str_replace('App\\Http\\Controllers\\', '', $controller),
        $method,
        implode(' ', array_map(
            fn ($mw) => preg_replace('#^.*\\\\#', '', $mw),
            $r['middleware'] ?? []
        )),
        $domain,
        $type,
        $statut,
    ];

    $stats[$domain][$type] = ($stats[$domain][$type] ?? 0) + 1;
    $stats[$domain]['_total'] = ($stats[$domain]['_total'] ?? 0) + 1;

    if ($domain === '???') {
        $unclassified[$controller] = ($unclassified[$controller] ?? 0) + 1;
    }
}

// Tri : par domaine, puis contrôleur, puis URI — lecture par lot de travail
usort($rows, fn ($a, $b) => [$a[6], $a[3], $a[1]] <=> [$b[6], $b[3], $b[1]]);

$fh = fopen($out, 'w');
fwrite($fh, "\xEF\xBB\xBF"); // BOM : Excel/fr ouvre correctement l'UTF-8
fputcsv($fh, ['method', 'uri', 'name', 'controller', 'action', 'middleware', 'domaine', 'type', 'statut'], ';');
foreach ($rows as $row) {
    fputcsv($fh, $row, ';');
}
fclose($fh);

// ---- Rapport de synthèse -----------------------------------------------------

$types = ['crud', 'action', 'vue', 'export', 'upload', 'framework'];
$md = "# Inventaire des endpoints — étape 1.0.1\n\n";
$md .= "> Généré par `scripts/build-endpoint-inventory.php` depuis `contracts/inventory/routes.json`.\n";
$md .= "> Source de vérité : `contracts/inventory/endpoints.csv`.\n\n";
$md .= '**Total : ' . count($rows) . " routes.**\n\n";
$md .= "## Répartition par domaine\n\n";
$md .= '| Domaine | Libellé | ' . implode(' | ', array_map('ucfirst', $types)) . " | **Total** |\n";
$md .= '|---|---|' . str_repeat('--:|', count($types)) . "--:|\n";

$codes = array_keys($stats);
sort($codes);
$grand = array_fill_keys($types, 0);

foreach ($codes as $code) {
    $cells = [];
    foreach ($types as $t) {
        $n = $stats[$code][$t] ?? 0;
        $grand[$t] += $n;
        $cells[] = $n ?: '·';
    }
    $md .= "| **$code** | " . ($domainLabels[$code] ?? '?') . ' | '
        . implode(' | ', $cells) . ' | **' . $stats[$code]['_total'] . "** |\n";
}
$md .= '| | **TOTAL** | ' . implode(' | ', array_map(fn ($t) => '**' . $grand[$t] . '**', $types))
    . ' | **' . count($rows) . "** |\n\n";

$aPorter = count(array_filter($rows, fn ($r) => $r[8] === 'à-porter'));
$md .= "## Charge de la phase 1\n\n";
$md .= "- **$aPorter endpoints à porter en API v1**\n";
$md .= '- ' . ($grand['vue'] ?? 0) . " routes `create`/`edit` abandonnées (remplacées par des écrans Next + endpoints `/options`)\n";
$md .= '- ' . ($grand['framework'] ?? 0) . " routes hors périmètre (Dusk, Sanctum, Ignition, Swagger)\n";
$md .= '- ' . ($grand['action'] ?? 0) . " **actions métier non-CRUD** — chacune doit devenir un `POST /api/v1/{ressource}/{id}/{verbe}` explicite (risque R06)\n";
$md .= '- ' . ($grand['export'] ?? 0) . ' exports et ' . ($grand['upload'] ?? 0) . " uploads — classes d'équivalence E2 en phase 3\n\n";

if ($unclassified !== []) {
    arsort($unclassified);
    $md .= "## ⚠️ Contrôleurs non classés (" . count($unclassified) . ")\n\n";
    $md .= "**Le critère de sortie de l'étape 1.0.1 est : cette section est vide.**\n";
    $md .= "Compléter la table `\$domains` de `scripts/build-endpoint-inventory.php`.\n\n";
    foreach ($unclassified as $ctrl => $n) {
        $md .= "- `" . str_replace('App\\Http\\Controllers\\', '', $ctrl) . "` — $n route(s)\n";
    }
    $md .= "\n";
}

file_put_contents($summary, $md);

echo "✓ contracts/inventory/endpoints.csv        (" . count($rows) . " routes)\n";
echo "✓ contracts/inventory/endpoints-summary.md\n";
echo "\n  à-porter    : $aPorter\n";
echo '  actions     : ' . ($grand['action'] ?? 0) . "\n";
echo '  non classés : ' . array_sum($unclassified) . ' route(s) sur ' . count($unclassified) . " contrôleur(s)\n";
