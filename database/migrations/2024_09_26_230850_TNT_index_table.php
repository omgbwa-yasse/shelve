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

        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_DATABASE', 'shelve_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'storage' => storage_path() . '/tntsearch',
            'fuzziness' => env('TNTSEARCH_FUZZINESS', false),
            'fuzzy_min_similarity' => env('TNTSEARCH_FUZZY_MIN_SIMILARITY', 0.1),
            'fuzzy_prefix_length' => env('TNTSEARCH_FUZZY_PREFIX_LENGTH', 2),
            'fuzzy_max_expansions' => env('TNTSEARCH_FUZZY_MAX_EXPANSIONS', 50),
            'as_you_type' => env('TNTSEARCH_AS_YOU_TYPE', false),
        ];

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
