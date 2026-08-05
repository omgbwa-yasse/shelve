<?php

/**
 * Phase 1 — Étape 1.0.2 : extraction des règles de validation existantes.
 *
 * Un `grep` ne suffit pas : les règles sont des tableaux multi-lignes. Ce script
 * lit les tokens PHP de chaque contrôleur, isole les appels de validation avec
 * leurs parenthèses équilibrées, et en extrait les couples champ → règles.
 *
 * Produit :
 *   contracts/inventory/validation-raw.txt    blocs bruts, avec fichier:ligne
 *   contracts/inventory/validation-rules.csv  contrôleur;méthode;champ;règles
 *   contracts/inventory/validation-gaps.md    ⚠️ écritures SANS validation (risque R01)
 *
 * Usage : php scripts/extract-validation-rules.php
 */

$root = dirname(__DIR__);
$controllerDir = $root . '/app/Http/Controllers';

/** Méthodes qui écrivent en base : elles doivent toutes valider leur entrée. */
const WRITE_METHODS = ['store', 'update', 'upload', 'import', 'attach'];

/** Appels considérés comme de la validation. */
const VALIDATION_CALLS = ['validate', 'validateWithBag', 'make', 'rules'];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerDir));
$blocks = [];        // blocs de validation trouvés
$methodsSeen = [];   // toutes les méthodes publiques des contrôleurs
$rows = [];          // lignes du CSV

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    $rel = str_replace(str_replace('\\', '/', $root) . '/', '', $path);
    $controller = basename($path, '.php');
    $src = file_get_contents($path);
    $tokens = token_get_all($src);

    $currentMethod = '';
    $braceDepth = 0;
    $methodBrace = -1;

    for ($i = 0, $n = count($tokens); $i < $n; $i++) {
        $t = $tokens[$i];

        // --- suivi de la méthode courante -----------------------------------
        if (is_array($t) && $t[0] === T_FUNCTION) {
            for ($j = $i + 1; $j < $n; $j++) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                    $currentMethod = $tokens[$j][1];
                    $methodBrace = $braceDepth;
                    $methodsSeen[$controller][$currentMethod] = [
                        'file' => $rel,
                        'line' => $t[2],
                        'validated' => $methodsSeen[$controller][$currentMethod]['validated'] ?? false,
                    ];
                    break;
                }
                if ($tokens[$j] === '(') {
                    break; // closure anonyme
                }
            }
        }

        if ($t === '{') {
            $braceDepth++;
        } elseif ($t === '}') {
            $braceDepth--;
            if ($currentMethod !== '' && $braceDepth <= $methodBrace) {
                $currentMethod = '';
            }
        }

        // --- détection d'un appel de validation ------------------------------
        if (!is_array($t) || $t[0] !== T_STRING || !in_array($t[1], VALIDATION_CALLS, true)) {
            continue;
        }

        // `make` n'est retenu que s'il s'agit de Validator::make
        if ($t[1] === 'make') {
            $prev = $tokens[$i - 2] ?? null;
            if (!is_array($prev) || !str_contains(strtolower($prev[1] ?? ''), 'validator')) {
                continue;
            }
        }
        // `rules` : la déclaration `function rules()` a des parenthèses vides et
        // sera écartée par le filtre `=>` plus bas ; seul son corps nous intéresse.

        // parenthèse ouvrante attendue juste après
        $k = $i + 1;
        while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
            $k++;
        }
        if (($tokens[$k] ?? null) !== '(') {
            continue;
        }

        // --- extraction du bloc à parenthèses équilibrées ---------------------
        $depth = 0;
        $raw = '';
        for ($j = $k; $j < $n; $j++) {
            $tok = $tokens[$j];
            $text = is_array($tok) ? $tok[1] : $tok;
            $raw .= $text;
            if ($tok === '(') {
                $depth++;
            } elseif ($tok === ')') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }
        }

        // On ne garde que les blocs contenant un tableau de règles
        if (!str_contains($raw, '=>')) {
            continue;
        }

        $line = $t[2];
        $blocks[] = [
            'file' => $rel,
            'line' => $line,
            'controller' => $controller,
            'method' => $currentMethod ?: '(hors méthode)',
            'raw' => trim($raw),
        ];

        if ($currentMethod !== '') {
            $methodsSeen[$controller][$currentMethod]['validated'] = true;
        }

        // --- extraction champ => règles ---------------------------------------
        // La virgule est significative DANS une règle (`exists:activities,id`) :
        // on capture donc la valeur jusqu'à la fin de ligne, sans la reformater.
        if (preg_match_all(
            "#['\"]([A-Za-z0-9_.*]+)['\"]\s*=>\s*([^\n]+)#",
            $raw,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $pair) {
                $value = rtrim(trim($pair[2]), ',');

                // Règle construite dynamiquement (concaténation, implode, constante
                // de classe) : la valeur littérale est incomplète, il faut la lire
                // à la source. Ne pas le signaler donnerait une fausse exhaustivité.
                $dynamic = (bool) preg_match('#\$|\.\s*[\'"$]|implode\(|::#', $value);

                if (str_starts_with($value, '[')) {
                    // tableau de règles → aplati en notation pipe
                    $inner = trim($value, "[], \t");
                    $parts = array_map(
                        fn ($p) => trim($p, " \t'\""),
                        preg_split('#\'\s*,\s*\'|"\s*,\s*"#', $inner) ?: []
                    );
                    $rules = implode('|', array_filter($parts));
                } else {
                    // chaîne de règles → conservée telle quelle, virgules comprises
                    $rules = trim($value, " \t'\"");
                }

                $rows[] = [
                    $controller,
                    $currentMethod ?: '?',
                    $pair[1],
                    $rules,
                    $dynamic ? 'oui' : '',
                    $rel . ':' . $line,
                ];
            }
        }
    }
}

// ---- validation-raw.txt ------------------------------------------------------

$txt = "# Blocs de validation extraits des contrôleurs — étape 1.0.2\n";
$txt .= '# ' . count($blocks) . " blocs trouvés\n";
$txt .= "# Généré par scripts/extract-validation-rules.php — ne pas éditer à la main\n\n";
foreach ($blocks as $b) {
    $txt .= str_repeat('─', 78) . "\n";
    $txt .= "{$b['controller']}::{$b['method']}()  —  {$b['file']}:{$b['line']}\n";
    $txt .= str_repeat('─', 78) . "\n";
    $txt .= $b['raw'] . "\n\n";
}
file_put_contents($root . '/contracts/inventory/validation-raw.txt', $txt);

// ---- validation-rules.csv ----------------------------------------------------

$fh = fopen($root . '/contracts/inventory/validation-rules.csv', 'w');
fwrite($fh, "\xEF\xBB\xBF");
fputcsv($fh, ['controller', 'method', 'champ', 'regles', 'dynamique', 'source'], ';');
usort($rows, fn ($a, $b) => [$a[0], $a[1], $a[2]] <=> [$b[0], $b[1], $b[2]]);
foreach ($rows as $r) {
    fputcsv($fh, $r, ';');
}
fclose($fh);

// ---- validation-gaps.md : le livrable critique -------------------------------

$gaps = [];
$covered = 0;
foreach ($methodsSeen as $controller => $methods) {
    foreach ($methods as $name => $info) {
        $isWrite = false;
        foreach (WRITE_METHODS as $w) {
            if (stripos($name, $w) === 0) {
                $isWrite = true;
                break;
            }
        }
        if (!$isWrite) {
            continue;
        }
        if ($info['validated']) {
            $covered++;
        } else {
            $gaps[] = [$controller, $name, $info['file'], $info['line']];
        }
    }
}

$totalWrites = $covered + count($gaps);
$pct = $totalWrites ? round($covered / $totalWrites * 100, 1) : 0;

$md = "# Couverture de validation des écritures — étape 1.0.2\n\n";
$md .= "> Généré par `scripts/extract-validation-rules.php`.\n";
$md .= "> Traite le **risque R01** (règles de validation perdues), criticité 20.\n\n";
$md .= "## Synthèse\n\n";
$md .= "| Indicateur | Valeur |\n|---|--:|\n";
$dynamicCount = count(array_filter($rows, fn ($r) => $r[4] === 'oui'));
$md .= "| Blocs de validation trouvés | " . count($blocks) . " |\n";
$md .= "| Couples champ → règles extraits | " . count($rows) . " |\n";
$md .= "| … dont **règles construites dynamiquement** (à relire à la source) | **$dynamicCount** |\n";
$md .= "| Méthodes d'écriture (`store`/`update`/`upload`/`import`/`attach`) | $totalWrites |\n";
$md .= "| … dont **validées** | $covered |\n";
$md .= "| … dont **SANS validation** | **" . count($gaps) . "** |\n";
$md .= "| **Couverture** | **$pct %** |\n\n";

$md .= "## Interprétation\n\n";
$md .= "Chaque ligne du tableau ci-dessous est une méthode qui écrit en base **sans valider son entrée**.\n";
$md .= "En Blade, le formulaire limitait implicitement ce qui arrivait (champs du formulaire, types HTML,\n";
$md .= "JavaScript côté client). Une API n'a aucune de ces protections : le portage tel quel exposerait\n";
$md .= "ces écritures à n'importe quel payload.\n\n";
$md .= "**Chacune exige une décision explicite** au moment du portage de son domaine :\n";
$md .= "règles reconstituées depuis le schéma de la table + le formulaire Blade correspondant.\n\n";
$md .= "Par ailleurs, **$dynamicCount règles sont construites dynamiquement** (concaténation avec un id,\n";
$md .= "`implode()` sur une constante de classe, `Rule::` …). Le CSV n'en contient que la partie littérale :\n";
$md .= "ces lignes portent `dynamique = oui` et **doivent être relues à la source** avant d'être portées.\n\n";

if ($gaps !== []) {
    usort($gaps, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);
    $md .= "## ⚠️ Écritures sans validation (" . count($gaps) . ")\n\n";
    $md .= "| Contrôleur | Méthode | Source |\n|---|---|---|\n";
    foreach ($gaps as $g) {
        $md .= "| `{$g[0]}` | `{$g[1]}()` | {$g[2]}:{$g[3]} |\n";
    }
}

file_put_contents($root . '/contracts/inventory/validation-gaps.md', $md);

echo "✓ contracts/inventory/validation-raw.txt    (" . count($blocks) . " blocs)\n";
echo "✓ contracts/inventory/validation-rules.csv  (" . count($rows) . " règles)\n";
echo "✓ contracts/inventory/validation-gaps.md\n";
echo "\n  écritures validées      : $covered / $totalWrites ($pct %)\n";
echo '  écritures NON validées  : ' . count($gaps) . "\n";
