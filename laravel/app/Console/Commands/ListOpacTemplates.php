<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PublicTemplate;

class ListOpacTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opac:list-templates {--status=active : Filter by status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liste tous les templates OPAC disponibles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');

        $query = PublicTemplate::where('type', 'opac');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $templates = $query->orderBy('name')->get();

        if ($templates->isEmpty()) {
            $this->info('Aucun template OPAC trouvé.');
            return 0;
        }

        $this->info("Templates OPAC disponibles :");
        $this->line("");

        $headers = ['ID', 'Nom', 'Description', 'Statut', 'Créé le'];
        $rows = [];

        foreach ($templates as $template) {
            $rows[] = [
                $template->id,
                $template->name,
                substr($template->description ?? '', 0, 50) . (strlen($template->description ?? '') > 50 ? '...' : ''),
                $template->status,
                $template->created_at->format('d/m/Y H:i'),
            ];
        }

        $this->table($headers, $rows);

        $this->line("");
        $this->info("Total : " . $templates->count() . " template(s)");

        if ($templates->where('status', 'active')->isNotEmpty()) {
            $this->line("");
            $this->comment("Pour tester un template :");
            $this->line("php artisan serve");
            $this->line("Puis visitez : http://localhost:8000/public/templates");
        }

        return 0;
    }
}
