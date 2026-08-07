<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#6b7280');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organisation_id', 'name']);
        });

        Schema::create('email_message_email_tag', function (Blueprint $table) {
            $table->foreignId('email_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['email_message_id', 'email_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_message_email_tag');
        Schema::dropIfExists('email_tags');
    }
};
