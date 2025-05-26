<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Seed ai_module
        DB::table('ai_module')->insert([
            ['name' => 'Chat Assistant', 'description' => 'Module de chat intelligent pour assistance utilisateur', 'is_active' => true, 'configuration' => json_encode(['max_tokens' => 4096, 'temperature' => 0.7]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Document Analyzer', 'description' => 'Analyse et traitement automatique de documents', 'is_active' => true, 'configuration' => json_encode(['supported_formats' => ['pdf', 'docx', 'txt']]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Content Generator', 'description' => 'Génération automatique de contenu et suggestions', 'is_active' => true, 'configuration' => json_encode(['creativity_level' => 'medium']), 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Seed ai_models (toutes les colonnes pour chaque ligne)
        DB::table('ai_models')->insert([
            [
                'name' => 'GPT-4',
                'provider' => 'OpenAI',
                'version' => '4.0',
                'model_family' => 'GPT',
                'parameter_size' => 1750000000000,
                'file_size' => null,
                'quantization' => null,
                'model_modified_at' => null,
                'digest' => null,
                'model_details' => null,
                'supports_streaming' => true,
                'max_context_length' => 8192,
                'default_temperature' => 0.70,
                'api_type' => 'chat',
                'capabilities' => json_encode(['text_generation', 'conversation', 'analysis']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Claude-3-Sonnet',
                'provider' => 'Anthropic',
                'version' => '3.0',
                'model_family' => 'Claude',
                'parameter_size' => null,
                'file_size' => null,
                'quantization' => null,
                'model_modified_at' => null,
                'digest' => null,
                'model_details' => null,
                'supports_streaming' => true,
                'max_context_length' => 200000,
                'default_temperature' => 0.70,
                'api_type' => 'chat',
                'capabilities' => json_encode(['text_generation', 'analysis', 'coding']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Llama2-7B',
                'provider' => 'Ollama',
                'version' => '7B',
                'model_family' => 'Llama',
                'parameter_size' => 7000000000,
                'file_size' => 3800000000,
                'quantization' => 'Q4_0',
                'model_modified_at' => $now,
                'digest' => 'sha256:1a838c0...',
                'model_details' => json_encode(['format' => 'gguf', 'family' => 'llama']),
                'supports_streaming' => true,
                'max_context_length' => 4096,
                'default_temperature' => 0.70,
                'api_type' => 'chat',
                'capabilities' => json_encode(['text_generation', 'conversation']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Mistral-7B',
                'provider' => 'Ollama',
                'version' => '7B-Instruct',
                'model_family' => 'Mistral',
                'parameter_size' => 7000000000,
                'file_size' => 4100000000,
                'quantization' => 'Q4_K_M',
                'model_modified_at' => $now,
                'digest' => 'sha256:2b949d...',
                'model_details' => json_encode(['format' => 'gguf', 'family' => 'mistral']),
                'supports_streaming' => true,
                'max_context_length' => 32768,
                'default_temperature' => 0.70,
                'api_type' => 'chat',
                'capabilities' => json_encode(['text_generation', 'instruction_following']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);

        // Seed ai_action_types
        DB::table('ai_action_types')->insert([
            ['name' => 'text_revision', 'display_name' => 'Révision de texte', 'description' => 'Correction et amélioration automatique de texte', 'required_fields' => json_encode(['original_text']), 'optional_fields' => json_encode(['style', 'tone']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'keyword_suggestion', 'display_name' => 'Suggestion de mots-clés', 'description' => 'Génération automatique de mots-clés pertinents', 'required_fields' => json_encode(['content']), 'optional_fields' => json_encode(['category', 'language']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'content_classification', 'display_name' => 'Classification de contenu', 'description' => 'Classification automatique du contenu par catégorie', 'required_fields' => json_encode(['content']), 'optional_fields' => json_encode(['categories']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'summary_generation', 'display_name' => 'Génération de résumé', 'description' => 'Création automatique de résumés de contenu', 'required_fields' => json_encode(['content']), 'optional_fields' => json_encode(['max_length', 'style']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Seed ai_prompt_templates (en supposant que l'utilisateur avec ID 1 existe)
        DB::table('ai_prompt_templates')->insert([
            ['name' => 'Correction de texte standard', 'description' => 'Template pour correction orthographique et grammaticale', 'template_content' => 'Corrige les erreurs d\'orthographe et de grammaire dans le texte suivant : {original_text}', 'action_type_id' => 1, 'variables' => json_encode(['original_text']), 'created_by' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Extraction de mots-clés', 'description' => 'Template pour extraction de mots-clés', 'template_content' => 'Extrais 5 à 10 mots-clés pertinents du contenu suivant : {content}', 'action_type_id' => 2, 'variables' => json_encode(['content']), 'created_by' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Classification de document', 'description' => 'Template pour classification automatique', 'template_content' => 'Classe le document suivant dans une des catégories : {categories}. Contenu : {content}', 'action_type_id' => 3, 'variables' => json_encode(['content', 'categories']), 'created_by' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}