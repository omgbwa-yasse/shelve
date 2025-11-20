<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('record_book_publisher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('record_books')->onDelete('cascade');
            $table->foreignId('publisher_id')->constrained('record_book_publishers')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['book_id', 'publisher_id']);
        });

        // Migrate existing data if any (assuming publisher_id exists on record_books)
        if (Schema::hasColumn('record_books', 'publisher_id')) {
            $books = DB::table('record_books')->whereNotNull('publisher_id')->get();
            foreach ($books as $book) {
                DB::table('record_book_publisher')->insert([
                    'book_id' => $book->id,
                    'publisher_id' => $book->publisher_id,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('record_books', function (Blueprint $table) {
                $table->dropForeign(['publisher_id']); // Drop foreign key constraint first
                $table->dropColumn('publisher_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            $table->foreignId('publisher_id')->nullable()->constrained('record_book_publishers');
        });

        // Restore data (pick the primary one)
        $relations = DB::table('record_book_publisher')->where('is_primary', true)->get();
        foreach ($relations as $relation) {
            DB::table('record_books')
                ->where('id', $relation->book_id)
                ->update(['publisher_id' => $relation->publisher_id]);
        }

        Schema::dropIfExists('record_book_publisher');
    }
};
