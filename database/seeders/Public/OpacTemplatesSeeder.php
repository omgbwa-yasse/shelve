<?php

namespace Database\Seeders\Public;

use App\Models\PublicTemplate;
use Illuminate\Database\Seeder;

class OpacTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = $this->getTemplateDefinitions();
        $this->createTemplates($templates);
    }

    /**
     * Get all template definitions
     */
    private function getTemplateDefinitions(): array
    {
        return [
            $this->getDefaultClassicDefinition(),
            $this->getModernMinimalDefinition(),
            $this->getAcademicProDefinition(),
            $this->getDarkThemeDefinition(),
            $this->getColorfulCreativeDefinition(),
        ];
    }

    /**
     * Create templates in database
     */
    private function createTemplates(array $templates): void
    {
        foreach ($templates as $template) {
            PublicTemplate::updateOrCreate(
                [
                    'name' => $template['name'],
                    'type' => 'opac'
                ],
                [
                    'description' => $template['description'],
                    'content' => $template['content'],
                    'variables' => $template['variables'],
                    'parameters' => $template['variables'], // Utilisation des variables comme paramÃ¨tres
                    'values' => [], // Valeurs par dÃ©faut vides
                    'status' => $template['status'],
                    'type' => 'opac',
                    'author_id' => 1,
                    'is_active' => $template['status'] === 'active',
                ]
            );
        }
    }

    private function getDefaultClassicDefinition(): array
    {
        return [
            'name' => 'Default Classic',
            'description' => 'Template OPAC classique avec navigation traditionnelle et couleurs professionnelles.',
            'content' => $this->getDefaultClassicTemplate(),
            'variables' => [
                'primary_color' => '#2c3e50',
                'secondary_color' => '#3498db',
                'accent_color' => '#e74c3c',
                'background_color' => '#ecf0f1',
                'text_color' => '#2c3e50',
                'custom_css' => '.opac-classic { font-family: "Times New Roman", serif; }'
            ],
            'status' => 'active',
        ];
    }

    private function getModernMinimalDefinition(): array
    {
        return [
            'name' => 'Modern Minimal',
            'description' => 'Design moderne et minimaliste avec beaucoup d\'espace blanc et typographie Ã©purÃ©e.',
            'content' => $this->getModernMinimalTemplate(),
            'variables' => [
                'primary_color' => '#ffffff',
                'secondary_color' => '#007bff',
                'accent_color' => '#28a745',
                'background_color' => '#f8f9fa',
                'text_color' => '#212529',
                'custom_css' => '.opac-minimal { font-family: "Helvetica Neue", Arial, sans-serif; }'
            ],
            'status' => 'active',
        ];
    }

    private function getAcademicProDefinition(): array
    {
        return [
            'name' => 'Academic Pro',
            'description' => 'Template orientÃ© acadÃ©mique avec mise en page structurÃ©e pour institutions Ã©ducatives.',
            'content' => $this->getAcademicProTemplate(),
            'variables' => [
                'primary_color' => '#1e3a8a',
                'secondary_color' => '#3b82f6',
                'accent_color' => '#f59e0b',
                'background_color' => '#f1f5f9',
                'text_color' => '#1e293b',
                'custom_css' => '.opac-academic { font-family: "Georgia", "Times New Roman", serif; }'
            ],
            'status' => 'active',
        ];
    }

    private function getDarkThemeDefinition(): array
    {
        return [
            'name' => 'Dark Theme',
            'description' => 'ThÃ¨me sombre Ã©lÃ©gant pour une expÃ©rience de navigation confortable en faible luminositÃ©.',
            'content' => $this->getDarkThemeTemplate(),
            'variables' => [
                'primary_color' => '#1a1a1a',
                'secondary_color' => '#333333',
                'accent_color' => '#ff6b35',
                'background_color' => '#0f0f0f',
                'text_color' => '#e0e0e0',
                'custom_css' => '.opac-dark { background-color: #0f0f0f; color: #e0e0e0; }'
            ],
            'status' => 'active',
        ];
    }

    private function getColorfulCreativeDefinition(): array
    {
        return [
            'name' => 'Colorful Creative',
            'description' => 'Template crÃ©atif avec couleurs vives et animations pour une expÃ©rience dynamique.',
            'content' => $this->getColorfulCreativeTemplate(),
            'variables' => [
                'primary_color' => '#e91e63',
                'secondary_color' => '#9c27b0',
                'accent_color' => '#ff9800',
                'background_color' => '#fafafa',
                'text_color' => '#424242',
                'custom_css' => '.opac-creative { font-family: "Poppins", sans-serif; }'
            ],
            'status' => 'active',
        ];
    }

    private function getDefaultClassicTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{locale}}" class="opac-classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{library_name}} - OPAC</title>
    <style>
        {{custom_css}}
        .classic-header {
            background: {{primary_color}};
            color: white;
            padding: 2rem 0;
        }
        .classic-nav {
            background: {{secondary_color}};
        }
        .classic-search {
            border: 2px solid {{secondary_color}};
            border-radius: 5px;
        }
    </style>
</head>
<body class="opac-classic">
    <header class="classic-header">
        <div class="container">
            <h1>{{library_name}}</h1>
            <p>Catalogue en ligne</p>
        </div>
    </header>

    <nav class="classic-nav">
        <div class="container">
            <!-- Navigation menu -->
        </div>
    </nav>

    <main class="container my-4">
        <div class="search-section">
            <h2>Recherche dans le catalogue</h2>
            <input type="text" class="form-control classic-search" placeholder="Entrez vos mots-clÃ©s...">
        </div>

        <div class="content-area">
            <!-- Main content -->
        </div>
    </main>

    <footer class="bg-dark text-light py-3">
        <div class="container">
            <p>&copy; {{current_date}} {{library_name}}</p>
        </div>
    </footer>
</body>
</html>';
    }

    private function getModernMinimalTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{locale}}" class="opac-minimal">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{library_name}}</title>
    <style>
        {{custom_css}}
        .minimal-header {
            background: {{primary_color}};
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        .minimal-search {
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            padding: 15px 20px;
        }
        .minimal-card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 10px;
        }
    </style>
</head>
<body class="opac-minimal">
    <header class="minimal-header">
        <div class="container">
            <h1 class="h3 mb-0">{{library_name}}</h1>
        </div>
    </header>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mb-4">Rechercher</h2>
                <input type="text" class="form-control minimal-search" placeholder="Que recherchez-vous ?">
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card minimal-card">
                    <div class="card-body">
                        <h5>Catalogue</h5>
                        <p>Parcourir nos collections</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>';
    }

    private function getAcademicProTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{locale}}" class="opac-academic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{library_name}} - BibliothÃ¨que AcadÃ©mique</title>
    <style>
        {{custom_css}}
        .academic-header {
            background: {{primary_color}};
            border-bottom: 4px solid {{accent_color}};
            color: white;
            padding: 2rem 0;
        }
        .academic-breadcrumb {
            background: {{secondary_color}};
            color: white;
            padding: 0.5rem 0;
        }
        .academic-section {
            border-left: 4px solid {{accent_color}};
            padding-left: 1rem;
            margin: 2rem 0;
        }
    </style>
</head>
<body class="opac-academic">
    <header class="academic-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>{{library_name}}</h1>
                    <p class="lead">Ressources acadÃ©miques et scientifiques</p>
                </div>
                <div class="col-md-4">
                    <div class="text-end">
                        <span class="badge bg-warning">{{total_records}} documents</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="academic-breadcrumb">
        <div class="container">
            Accueil > Catalogue
        </div>
    </nav>

    <main class="container my-4">
        <div class="academic-section">
            <h2>Recherche avancÃ©e</h2>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Titre, auteur, mots-clÃ©s...">
                </div>
                <div class="col-md-3">
                    <select class="form-control">
                        <option>Tous les types</option>
                        <option>Livres</option>
                        <option>Articles</option>
                        <option>ThÃ¨ses</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100">Rechercher</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>';
    }

    private function getDarkThemeTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{locale}}" class="opac-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{library_name}} - Mode Sombre</title>
    <style>
        {{custom_css}}
        body {
            background-color: {{background_color}};
            color: {{text_color}};
        }
        .dark-header {
            background: {{primary_color}};
            border-bottom: 2px solid {{accent_color}};
            padding: 1.5rem 0;
        }
        .dark-card {
            background: {{secondary_color}};
            border: 1px solid #444;
            border-radius: 8px;
        }
        .dark-search {
            background: {{secondary_color}};
            border: 1px solid #555;
            color: {{text_color}};
        }
    </style>
</head>
<body class="opac-dark">
    <header class="dark-header">
        <div class="container">
            <h1 style="color: {{accent_color}}">{{library_name}}</h1>
            <p>Catalogue numÃ©rique - Mode nuit</p>
        </div>
    </header>

    <main class="container my-4">
        <div class="row">
            <div class="col-md-8">
                <div class="dark-card p-4">
                    <h2 style="color: {{accent_color}}">Recherche</h2>
                    <input type="text" class="form-control dark-search" placeholder="Rechercher dans {{total_records}} documents...">

                    <div class="mt-3">
                        <span class="badge" style="background: {{accent_color}}">NouveautÃ©s</span>
                        <span class="badge" style="background: {{secondary_color}}">Populaires</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dark-card p-3">
                    <h5 style="color: {{accent_color}}">AccÃ¨s rapide</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" style="color: {{text_color}}">â€¢ Catalogue</a></li>
                        <li><a href="#" style="color: {{text_color}}">â€¢ RÃ©servations</a></li>
                        <li><a href="#" style="color: {{text_color}}">â€¢ Mon compte</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</body>
</html>';
    }

    private function getColorfulCreativeTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{locale}}" class="opac-creative">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{library_name}} - CrÃ©atif</title>
    <style>
        {{custom_css}}
        .creative-header {
            background: linear-gradient(45deg, {{primary_color}}, {{secondary_color}}, {{accent_color}});
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            padding: 3rem 0;
            color: white;
        }
        .creative-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .creative-card:hover {
            transform: translateY(-5px);
        }
        .creative-search {
            border-radius: 25px;
            border: 3px solid {{primary_color}};
            padding: 15px 20px;
        }
        .creative-btn {
            background: linear-gradient(45deg, {{primary_color}}, {{accent_color}});
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body class="opac-creative">
    <header class="creative-header text-center">
        <div class="container">
            <h1 class="display-4 mb-3">{{library_name}}</h1>
            <p class="lead">DÃ©couvrez, explorez, apprenez !</p>
        </div>
    </header>

    <main class="container my-5">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="text-center">
                    <h2 style="color: {{primary_color}}">ðŸ” Que voulez-vous dÃ©couvrir ?</h2>
                    <div class="mt-3">
                        <input type="text" class="form-control creative-search" placeholder="Tapez ici pour commencer votre aventure...">
                        <button class="btn creative-btn mt-3">Partir Ã  la dÃ©couverte !</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card creative-card" style="background: linear-gradient(135deg, {{primary_color}}, transparent);">
                    <div class="card-body text-white">
                        <h5>ðŸ“š Livres</h5>
                        <p>Explorez notre collection</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card creative-card" style="background: linear-gradient(135deg, {{secondary_color}}, transparent);">
                    <div class="card-body text-white">
                        <h5>ðŸŽµ MultimÃ©dia</h5>
                        <p>Audio, vidÃ©o et plus</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card creative-card" style="background: linear-gradient(135deg, {{accent_color}}, transparent);">
                    <div class="card-body text-white">
                        <h5>ðŸ”¬ Recherche</h5>
                        <p>Ressources acadÃ©miques</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>';
    }
}

