<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TeamTNT\TNTSearch\TNTSearch;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tnt = new TNTSearch;

        $dbDriver = config('database.default', 'mysql');
        $dbDatabase = config('database.connections.' . $dbDriver . '.database');

        if ($dbDriver === 'sqlite') {
            $config = [
                'driver'                => 'sqlite',
                'database'              => $dbDatabase,
                'storage'               => storage_path() . '/tntsearch',
                'fuzziness'             => false,
                'fuzzy_min_similarity'  => 0.1,
                'fuzzy_prefix_length'   => 2,
                'fuzzy_max_expansions'  => 50,
                'as_you_type'           => false,
            ];
        } else {
            $config = [
                'driver'                => $dbDriver,
                'host'                  => config('database.connections.' . $dbDriver . '.host', '127.0.0.1'),
                'port'                  => config('database.connections.' . $dbDriver . '.port', '3306'),
                'database'              => $dbDatabase,
                'username'              => config('database.connections.' . $dbDriver . '.username', 'root'),
                'password'              => config('database.connections.' . $dbDriver . '.password', ''),
                'storage'               => storage_path() . '/tntsearch',
                'fuzziness'             => false,
                'fuzzy_min_similarity'  => 0.1,
                'fuzzy_prefix_length'   => 2,
                'fuzzy_max_expansions'  => 50,
                'as_you_type'           => false,
            ];
        }

        // Créer le dossier storage/tntsearch s'il n'existe pas
        $storagePath = storage_path() . '/tntsearch';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0775, true);
        }

        $tnt->loadConfig($config);
        $tnt->createIndex('products.title');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tnt = new TNTSearch;

        $tnt->loadConfig([
            'storage' => storage_path() . '/tntsearch',
        ]);

        $tnt->deleteIndex('products.title');
    }
};
