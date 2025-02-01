<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiModuleTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('prompts');
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('ai_model_configs');


        // Prompts
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('instruction');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_draft')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_system')->default(false);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });









        // Agents AI
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique(true);
            $table->text('description');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->date('date_exact')->nullable();
            $table->enum('date_type', ['start_only', 'exact', 'range'])->default('start_only');
            $table->enum('frequence_type', ['day', 'heure', 'min'])->default('day');
            $table->integer('frequence_value');
            $table->foreignId('prompt_id')->constrained('prompts');
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_trained')->default(true);
            $table->timestamps();
        });


        Schema::create('model_configs', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('config_key');
            $table->text('value');
            $table->timestamps();
        });


    }

    public function down()
    {
        Schema::dropIfExists('ai_model_configs');
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('prompts');
    }
}
