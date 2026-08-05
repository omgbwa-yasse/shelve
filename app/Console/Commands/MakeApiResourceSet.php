<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Génère le jeu de fichiers d'une ressource d'API v1 (phase 1, étape 1.0.5).
 *
 * Sans cet outil, exposer 184 modèles représente plus d'un millier de fichiers écrits
 * à la main, avec la dérive de conventions que cela implique.
 *
 * Ce qui est généré :
 *   app/Http/Controllers/Api/V1/{Model}Controller.php
 *   app/Http/Requests/Api/V1/{Model}/Store{Model}Request.php
 *   app/Http/Requests/Api/V1/{Model}/Update{Model}Request.php
 *   app/Http/Resources/Api/V1/{Model}Resource.php
 *   tests/Feature/Api/V1/{Model}ApiTest.php
 *
 * Les règles de validation et la liste des champs sont pré-remplies à partir du schéma
 * réel de la table (colonnes, nullabilité, longueurs, clés étrangères) ET des règles
 * déjà présentes dans le contrôleur Blade, extraites à l'étape 1.0.2.
 *
 * Le résultat est un POINT DE DÉPART, pas un livrable : chaque fichier généré porte un
 * bandeau `@generated` à retirer après relecture. Le schéma ne dit pas tout — les règles
 * métier, elles, sont dans le contrôleur Blade et dans les vues (risques R01 et R02).
 *
 * Usage :
 *   php artisan make:api-resource-set Activity --domain=D01
 *   php artisan make:api-resource-set Record --domain=D02 --force
 */
class MakeApiResourceSet extends Command
{
    protected $signature = 'make:api-resource-set
                            {model : Nom du modèle Eloquent (ex. Activity)}
                            {--domain= : Domaine D01–D16 (cf. evolution/README.md)}
                            {--force : Écraser les fichiers existants}';

    protected $description = "Génère contrôleur, requests, resource et test d'une ressource d'API v1";

    /** Colonnes jamais exposées ni acceptées en écriture. */
    private const HIDDEN_COLUMNS = ['password', 'remember_token'];

    /** Colonnes gérées par la persistance, absentes des formulaires. */
    private const MANAGED_COLUMNS = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function handle(): int
    {
        $model = Str::studly($this->argument('model'));
        $class = "App\\Models\\$model";

        if (!class_exists($class)) {
            $this->error("Modèle introuvable : $class");

            return self::FAILURE;
        }

        /** @var Model $instance */
        $instance = new $class();
        $table = $instance->getTable();

        if (!Schema::hasTable($table)) {
            $this->error("Table introuvable : $table");

            return self::FAILURE;
        }

        $domain = $this->option('domain') ?: '???';
        $columns = $this->describeTable($table);
        $existingRules = $this->existingValidationRules($model);

        $this->components->info("Ressource $model (table `$table`, domaine $domain)");

        $written = 0;
        foreach ($this->files($model, $table, $domain, $columns, $existingRules) as $path => $contents) {
            $written += $this->writeFile($path, $contents) ? 1 : 0;
        }

        $this->newLine();
        $this->components->info("$written fichier(s) écrit(s).");

        $plural = Str::kebab(Str::pluralStudly($model));
        $this->line('  Route à déclarer dans routes/api.php :');
        $this->newLine();
        $this->line("      Route::apiResource('$plural', {$model}Controller::class)");
        $this->line("          ->except(['create', 'edit']);");
        $this->newLine();

        $this->line('  À faire ensuite :');
        $this->line('   1. relire les règles générées contre le contrôleur Blade et ses vues (R01, R02)');
        $this->line('   2. restreindre les champs filtrables/triables/incluables (CONVENTIONS §3)');
        $this->line('   3. brancher la Policy existante dans le contrôleur (R04)');
        $this->line('   4. compléter le test d\'isolation multi-organisation (R03)');
        $this->line('   5. retirer le bandeau @generated de chaque fichier relu');
        $this->line('   6. publier le fragment OpenAPI');

        return self::SUCCESS;
    }

    /**
     * Décrit les colonnes de la table : type, nullabilité, longueur, valeur par défaut.
     * C'est la seule source fiable — les $fillable des modèles divergent parfois du
     * schéma (constaté sur Role::$fillable, qui déclarait une colonne inexistante).
     */
    private function describeTable(string $table): array
    {
        $columns = [];

        foreach (Schema::getColumns($table) as $col) {
            $name = $col['name'];

            if (in_array($name, self::HIDDEN_COLUMNS, true)) {
                continue;
            }

            $type = $col['type'] ?? $col['type_name'] ?? 'string';

            $columns[$name] = [
                'name' => $name,
                'type_name' => $col['type_name'] ?? 'string',
                'type' => $type,
                'nullable' => (bool) ($col['nullable'] ?? true),
                'default' => $col['default'] ?? null,
                'auto_increment' => (bool) ($col['auto_increment'] ?? false),
                'length' => $this->extractLength($type),
                'managed' => in_array($name, self::MANAGED_COLUMNS, true),
            ];
        }

        // Clés étrangères → règles `exists:`
        foreach (Schema::getForeignKeys($table) as $fk) {
            foreach ($fk['columns'] as $i => $column) {
                if (isset($columns[$column])) {
                    $columns[$column]['foreign'] = [
                        'table' => $fk['foreign_table'],
                        'column' => $fk['foreign_columns'][$i] ?? 'id',
                    ];
                }
            }
        }

        return $columns;
    }

    private function extractLength(string $type): ?int
    {
        return preg_match('#\((\d+)\)#', $type, $m) ? (int) $m[1] : null;
    }

    /**
     * Récupère les règles déjà écrites dans le contrôleur Blade, extraites en 1.0.2.
     * Elles priment sur celles déduites du schéma : elles portent l'intention métier
     * (unicité conditionnelle, `required_if`, valeurs énumérées…) que le schéma ignore.
     */
    private function existingValidationRules(string $model): array
    {
        $csv = base_path('contracts/inventory/validation-rules.csv');

        if (!is_file($csv)) {
            return [];
        }

        $rules = [];
        $handle = fopen($csv, 'r');
        fgetcsv($handle, 0, ';'); // en-tête

        $controller = $model . 'Controller';

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 5 || $row[0] !== $controller) {
                continue;
            }
            [, $method, $field, $rule, $dynamic] = $row;
            $rules[$method][$field] = ['rule' => $rule, 'dynamic' => $dynamic === 'oui'];
        }

        fclose($handle);

        return $rules;
    }

    /** Règles déduites du schéma, utilisées à défaut de règle existante. */
    private function inferRules(array $col, bool $isUpdate): array
    {
        $rules = [];

        $rules[] = $isUpdate
            ? 'sometimes'
            : ($col['nullable'] || $col['default'] !== null ? 'nullable' : 'required');

        $t = strtolower($col['type_name']);

        $rules[] = match (true) {
            isset($col['foreign']) => 'integer',
            str_contains($t, 'int') && $t !== 'point' => 'integer',
            str_contains($t, 'bool') || $col['type'] === 'tinyint(1)' => 'boolean',
            str_contains($t, 'decimal'), str_contains($t, 'float'), str_contains($t, 'double') => 'numeric',
            str_contains($t, 'datetime'), str_contains($t, 'timestamp') => 'date',
            $t === 'date' => 'date',
            str_contains($t, 'json') => 'array',
            str_contains($t, 'text') => 'string',
            default => 'string',
        };

        if ($col['length'] && str_contains($t, 'char')) {
            $rules[] = 'max:' . $col['length'];
        }

        if (isset($col['foreign'])) {
            $rules[] = "exists:{$col['foreign']['table']},{$col['foreign']['column']}";
        }

        return $rules;
    }

    private function files(
        string $model,
        string $table,
        string $domain,
        array $columns,
        array $existingRules
    ): array {
        $var = Str::camel($model);
        $plural = Str::kebab(Str::pluralStudly($model));

        return [
            app_path("Http/Requests/Api/V1/$model/Store{$model}Request.php")
                => $this->requestStub($model, $domain, $columns, $existingRules, false),
            app_path("Http/Requests/Api/V1/$model/Update{$model}Request.php")
                => $this->requestStub($model, $domain, $columns, $existingRules, true),
            app_path("Http/Resources/Api/V1/{$model}Resource.php")
                => $this->resourceStub($model, $domain, $columns),
            app_path("Http/Controllers/Api/V1/{$model}Controller.php")
                => $this->controllerStub($model, $var, $plural, $domain, $columns),
            base_path("tests/Feature/Api/V1/{$model}ApiTest.php")
                => $this->testStub($model, $plural, $domain, $columns),
        ];
    }

    private function banner(string $domain, string $extra = ''): string
    {
        return <<<PHP
/**
 * @generated par `php artisan make:api-resource-set` — domaine $domain.
 *
 * CE FICHIER EST UN POINT DE DÉPART, PAS UN LIVRABLE.
 * Les règles ci-dessous sont déduites du schéma et des règles déjà présentes dans le
 * contrôleur Blade. Le schéma ne connaît ni les règles métier ni ce que la vue imposait
 * implicitement (risques R01 et R02) : relire le contrôleur ET ses vues avant de valider.
 *
 * Retirer ce bandeau une fois le fichier relu.$extra
 */
PHP;
    }

    private function requestStub(
        string $model,
        string $domain,
        array $columns,
        array $existingRules,
        bool $isUpdate
    ): string {
        $action = $isUpdate ? 'Update' : 'Store';
        $method = $isUpdate ? 'update' : 'store';
        $lines = [];
        $notes = [];

        foreach ($columns as $name => $col) {
            if ($col['managed'] || $col['auto_increment']) {
                continue;
            }

            $existing = $existingRules[$method][$name] ?? null;

            if ($existing && $existing['dynamic']) {
                // Règle construite dynamiquement dans le contrôleur Blade : sa forme
                // littérale est incomplète et serait FAUSSE si on l'activait. On la
                // laisse en commentaire, remplacée par la règle déduite du schéma :
                // une règle absente et signalée vaut mieux qu'une règle plausible et
                // fausse, qui passerait la relecture sans se voir.
                $raw = addslashes($existing['rule']);
                $inferred = implode("', '", $this->inferRules($col, $isUpdate));
                $lines[] = "            // TODO règle d'origine, à reconstituer : '$raw'";
                $lines[] = "            '$name' => ['$inferred'],  // ⚠️ déduit du schéma, INCOMPLET";
                $notes[] = $name;
            } elseif ($existing) {
                $rule = addslashes($existing['rule']);
                $lines[] = "            '$name' => '$rule',  // reprise du contrôleur Blade";
            } else {
                $inferred = implode("', '", $this->inferRules($col, $isUpdate));
                $lines[] = "            '$name' => ['$inferred'],  // déduit du schéma";
            }
        }

        $rules = implode("\n", $lines);
        $extra = $notes
            ? "\n *\n * ⚠️ Règles dynamiques à reconstituer : " . implode(', ', $notes) . '.'
            : '';
        $banner = $this->banner($domain, $extra);

        return <<<PHP
<?php

namespace App\Http\Requests\Api\V1\\$model;

use Illuminate\Foundation\Http\FormRequest;

$banner
class {$action}{$model}Request extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
$rules
        ];
    }
}

PHP;
    }

    private function resourceStub(string $model, string $domain, array $columns): string
    {
        $lines = [];

        foreach ($columns as $name => $col) {
            $t = strtolower($col['type_name']);

            // Les accolades sont indispensables : sans elles, PHP interprète
            // `$name?->methode` comme un accès de propriété au moment de générer
            // la chaîne, au lieu de l'écrire littéralement dans le fichier produit.
            //
            // CONVENTIONS §5 : dates en ISO-8601 UTC, booléens en vrais booléens,
            // décimaux en chaînes pour ne pas perdre de précision.
            if (str_contains($t, 'datetime') || str_contains($t, 'timestamp')) {
                $lines[] = "            '{$name}' => \$this->{$name}?->toIso8601ZuluString(),";
            } elseif ($t === 'date') {
                // Colonne `date` : selon que le modèle la caste ou non, Eloquent renvoie
                // un Carbon ou une chaîne. Carbon::parse() couvre les deux cas.
                $lines[] = "            '{$name}' => \$this->{$name}"
                    . " ? Carbon::parse(\$this->{$name})->toDateString() : null,";
            } elseif ($col['type'] === 'tinyint(1)' || str_contains($t, 'bool')) {
                $lines[] = "            '{$name}' => (bool) \$this->{$name},";
            } elseif (str_contains($t, 'decimal')) {
                $lines[] = "            '{$name}' => \$this->{$name} === null"
                    . " ? null : (string) \$this->{$name},";
            } else {
                $lines[] = "            '{$name}' => \$this->{$name},";
            }
        }

        $fields = implode("\n", $lines);
        $banner = $this->banner($domain);

        return <<<PHP
<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

$banner
class {$model}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
$fields
        ];
    }
}

PHP;
    }

    private function controllerStub(
        string $model,
        string $var,
        string $plural,
        string $domain,
        array $columns
    ): string {
        $filterable = collect($columns)
            ->reject(fn ($c) => in_array($c['type_name'], ['text', 'json', 'blob'], true))
            ->keys()
            ->map(fn ($n) => "'$n'")
            ->implode(', ');

        $banner = $this->banner($domain);

        return <<<PHP
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\\$model\Store{$model}Request;
use App\Http\Requests\Api\V1\\$model\Update{$model}Request;
use App\Http\Resources\Api\V1\\{$model}Resource;
use App\Models\\$model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

$banner
class {$model}Controller extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = [$filterable];
    private const SORTABLE = [$filterable];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/$plural
     */
    public function index(Request \$request): JsonResponse
    {
        \$this->authorize('viewAny', $model::class);

        \$query = $model::query();

        \$this->applyFilters(\$query, \$request, self::FILTERABLE);
        \$this->applySorting(\$query, \$request, self::SORTABLE);
        \$this->applyIncludes(\$query, \$request, self::INCLUDABLE);

        \$page = \$query->paginate(\$this->pageSize(\$request))->withQueryString();

        return response()->json(\$this->paginatedResponse(\$page, {$model}Resource::class));
    }

    /**
     * GET /api/v1/$plural/{id}
     */
    public function show($model \$$var): JsonResponse
    {
        \$this->authorize('view', \$$var);

        return response()->json(['data' => new {$model}Resource(\$$var)]);
    }

    /**
     * POST /api/v1/$plural
     */
    public function store(Store{$model}Request \$request): JsonResponse
    {
        \$this->authorize('create', $model::class);

        \$$var = $model::create(\$request->validated());

        return response()->json(
            ['data' => new {$model}Resource(\$$var)],
            201,
            ['Location' => "/api/v1/$plural/{\${$var}->id}"]
        );
    }

    /**
     * PATCH /api/v1/$plural/{id}
     */
    public function update(Update{$model}Request \$request, $model \$$var): JsonResponse
    {
        \$this->authorize('update', \$$var);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        \${$var}->update(\$request->validated());

        return response()->json(['data' => new {$model}Resource(\${$var}->fresh())]);
    }

    /**
     * DELETE /api/v1/$plural/{id}
     */
    public function destroy($model \$$var): Response
    {
        \$this->authorize('delete', \$$var);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        \${$var}->delete();

        return response()->noContent();
    }
}

PHP;
    }

    private function testStub(string $model, string $plural, string $domain, array $columns): string
    {
        $banner = $this->banner($domain);
        $permissionPrefix = Str::snake($model);

        return <<<PHP
<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\\$model;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

$banner
class {$model}ApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    /**
     * Préfixe des permissions pour cette ressource (voir {$model}Policy).
     *
     * ⚠️ Déduit de \$model par snake_case — À VÉRIFIER : certaines Policies
     * réutilisent le préfixe d'une ressource parente (ex. LawArticlePolicy utilise
     * 'law_*', pas 'law_article_*'). Ouvrir app/Policies/{$model}Policy.php pour
     * confirmer avant de faire confiance à ce test.
     */
    private const PERMISSIONS = ['$permissionPrefix'];

    private User \$user;

    protected function setUp(): void
    {
        parent::setUp();

        \$organisation = Organisation::factory()->create();
        \$this->user = User::factory()->forOrganisation(\$organisation)->create();
        \$this->grantD01Permissions(\$this->user, self::PERMISSIONS);
    }

    public function test_index_exige_une_authentification(): void
    {
        \$this->getJson('/api/v1/$plural')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        \$user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        \$this->actingAs(\$user, 'sanctum')
            ->getJson('/api/v1/$plural')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        \$this->actingAs(\$this->user, 'sanctum')
            ->getJson('/api/v1/$plural')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        \$this->actingAs(\$this->user, 'sanctum')
            ->postJson('/api/v1/$plural', [])
            ->assertStatus(422);
    }

    // TODO show, update, destroy, filtres, tri, isolation multi-organisation (R03),
    // actions métier — compléter selon la ressource (voir D01/D03 pour le gabarit).
}

PHP;
    }

    private function writeFile(string $path, string $contents): bool
    {
        $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);

        if (File::exists($path) && !$this->option('force')) {
            $this->components->twoColumnDetail($relative, '<fg=yellow>existe déjà</>');

            return false;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);

        $this->components->twoColumnDetail($relative, '<fg=green>écrit</>');

        return true;
    }
}
