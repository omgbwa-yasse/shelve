<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template->name }} - Aperçu OPAC</title>

    <style>
        :root {
            --primary-color: {{ $template->variables['primary_color'] ?? '#007bff' }};
            --secondary-color: {{ $template->variables['secondary_color'] ?? '#6c757d' }};
            --accent-color: {{ $template->variables['accent_color'] ?? '#28a745' }};
            --font-family: {{ $template->variables['font_family'] ?? 'Inter, sans-serif' }};
            --border-radius: {{ $template->variables['border_radius'] ?? '4px' }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .opac-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .opac-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .opac-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Navigation */
        .opac-nav {
            background: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-links {
            display: flex;
            gap: 20px;
            list-style: none;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--secondary-color);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: var(--primary-color);
            color: white;
        }

        /* Search Section */
        .search-section {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            max-width: 800px;
        }

        .search-input {
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-family: var(--font-family);
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-btn:hover {
            background: color-mix(in srgb, var(--primary-color) 80%, black);
        }

        /* Advanced Search */
        .advanced-search {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .advanced-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
        }

        .field-group label {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--secondary-color);
        }

        .field-group select,
        .field-group input {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            font-family: var(--font-family);
        }

        /* Results Section */
        .results-section {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .results-header {
            background: var(--secondary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-count {
            font-weight: 500;
        }

        .results-sort {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .results-sort select {
            padding: 5px 10px;
            border: none;
            border-radius: var(--border-radius);
            background: white;
        }

        .results-list {
            padding: 20px;
        }

        /* Result Item */
        .result-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .result-title a {
            color: inherit;
            text-decoration: none;
        }

        .result-title a:hover {
            text-decoration: underline;
        }

        .result-meta {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .result-description {
            color: #555;
            line-height: 1.5;
        }

        .result-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .result-actions .btn {
            padding: 6px 12px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: var(--accent-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            color: var(--secondary-color);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .current {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Footer */
        .opac-footer {
            margin-top: 50px;
            text-align: center;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .nav-links {
                justify-content: center;
            }

            .results-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .advanced-fields {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="opac-header">
            <h1>{{ $template->name }}</h1>
            <p>Catalogue public en ligne - Interface personnalisée</p>
        </header>

        <!-- Navigation -->
        <nav class="opac-nav">
            <ul class="nav-links">
                <li><a href="#" class="active">Recherche simple</a></li>
                <li><a href="#">Recherche avancée</a></li>
                <li><a href="#">Parcourir</a></li>
                <li><a href="#">Nouveautés</a></li>
                <li><a href="#">Collections</a></li>
                <li><a href="#">Aide</a></li>
            </ul>
        </nav>

        <!-- Search Section -->
        <section class="search-section">
            <form class="search-form">
                <input type="text" class="search-input" placeholder="Rechercher dans le catalogue..." value="architecture moderne">
                <button type="submit" class="search-btn">Rechercher</button>
            </form>

            <div class="advanced-search">
                <h3 style="color: var(--secondary-color); margin-bottom: 15px;">Filtres de recherche</h3>
                <div class="advanced-fields">
                    <div class="field-group">
                        <label for="author">Auteur</label>
                        <input type="text" id="author" name="author">
                    </div>
                    <div class="field-group">
                        <label for="subject">Sujet</label>
                        <select id="subject" name="subject">
                            <option value="">Tous les sujets</option>
                            <option value="architecture">Architecture</option>
                            <option value="histoire">Histoire</option>
                            <option value="sciences">Sciences</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="year">Année de publication</label>
                        <input type="number" id="year" name="year" placeholder="2020">
                    </div>
                    <div class="field-group">
                        <label for="type">Type de document</label>
                        <select id="type" name="type">
                            <option value="">Tous les types</option>
                            <option value="livre">Livre</option>
                            <option value="article">Article</option>
                            <option value="these">Thèse</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <!-- Results Section -->
        <section class="results-section">
            <div class="results-header">
                <div class="results-count">
                    147 résultats trouvés pour "architecture moderne"
                </div>
                <div class="results-sort">
                    <label for="sort">Trier par:</label>
                    <select id="sort" name="sort">
                        <option value="relevance">Pertinence</option>
                        <option value="date">Date</option>
                        <option value="title">Titre</option>
                        <option value="author">Auteur</option>
                    </select>
                </div>
            </div>

            <div class="results-list">
                <!-- Result 1 -->
                <article class="result-item">
                    <h2 class="result-title">
                        <a href="#">L'architecture moderne : principes et évolutions</a>
                    </h2>
                    <div class="result-meta">
                        Par <strong>Jean Dupont</strong> • Publié en 2022 • Livre • Disponible
                    </div>
                    <div class="result-description">
                        Une exploration complète des mouvements architecturaux du XXe siècle, analysant les innovations techniques et esthétiques qui ont façonné nos villes modernes. L'auteur propose une synthèse accessible des grands courants...
                    </div>
                    <div class="result-actions">
                        <a href="#" class="btn btn-primary">Voir la fiche</a>
                        <a href="#" class="btn btn-secondary">Réserver</a>
                    </div>
                </article>

                <!-- Result 2 -->
                <article class="result-item">
                    <h2 class="result-title">
                        <a href="#">Bauhaus et modernité : une révolution esthétique</a>
                    </h2>
                    <div class="result-meta">
                        Par <strong>Marie Martin</strong> • Publié en 2021 • Livre • Emprunté
                    </div>
                    <div class="result-description">
                        Retour sur l'école du Bauhaus et son influence considérable sur l'architecture contemporaine. Cette étude détaillée examine les innovations pédagogiques et créatives de cette institution légendaire...
                    </div>
                    <div class="result-actions">
                        <a href="#" class="btn btn-primary">Voir la fiche</a>
                        <a href="#" class="btn btn-secondary">Liste d'attente</a>
                    </div>
                </article>

                <!-- Result 3 -->
                <article class="result-item">
                    <h2 class="result-title">
                        <a href="#">Urbanisme et architecture durable</a>
                    </h2>
                    <div class="result-meta">
                        Par <strong>Pierre Dubois</strong> • Publié en 2023 • Article • En ligne
                    </div>
                    <div class="result-description">
                        Les défis de l'architecture contemporaine face aux enjeux environnementaux. Cet article analyse les nouvelles approches durables dans la conception architecturale et urbaine...
                    </div>
                    <div class="result-actions">
                        <a href="#" class="btn btn-primary">Lire en ligne</a>
                        <a href="#" class="btn btn-secondary">Télécharger PDF</a>
                    </div>
                </article>

                <!-- Result 4 -->
                <article class="result-item">
                    <h2 class="result-title">
                        <a href="#">Histoire de l'architecture moderne en France</a>
                    </h2>
                    <div class="result-meta">
                        Par <strong>Sophie Leroux</strong> • Publié en 2020 • Thèse • Disponible
                    </div>
                    <div class="result-description">
                        Thèse de doctorat portant sur l'évolution de l'architecture moderne française de 1900 à nos jours. Une analyse approfondie des influences culturelles et techniques sur les pratiques architecturales...
                    </div>
                    <div class="result-actions">
                        <a href="#" class="btn btn-primary">Consulter</a>
                        <a href="#" class="btn btn-secondary">Citer</a>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <span>Précédent</span>
                <span class="current">1</span>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">4</a>
                <a href="#">5</a>
                <a href="#">Suivant</a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="opac-footer">
            <p>&copy; 2024 Catalogue Public • Powered by {{ $template->name }}</p>
        </footer>
    </div>

    <script>
        // Simulation d'interactivité
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des liens de navigation
            const navLinks = document.querySelectorAll('.nav-links a');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Simulation de recherche
            const searchForm = document.querySelector('.search-form');
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Recherche simulée - Cette fonctionnalité sera disponible une fois le template intégré.');
            });

            // Actions sur les résultats
            const actionButtons = document.querySelectorAll('.result-actions .btn');
            actionButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Action simulée: ' + this.textContent);
                });
            });
        });
    </script>
</body>
</html>
