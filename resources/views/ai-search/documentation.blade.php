@extends('layouts.app')

@section('title', __('AI Search Documentation'))

@section('content')
<div class="card-header bg-info text-white">
    <h4 class="mb-0">
        <i class="bi bi-book me-2"></i>
        {{ __('AI Search Documentation') }}
    </h4>
</div>
<div class="card-body">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar Navigation -->
            <div class="list-group sticky-top" style="top: 20px;">
                <a href="#overview" class="list-group-item list-group-item-action">
                    <i class="bi bi-info-circle me-2"></i>Vue d'ensemble
                </a>
                <a href="#capabilities" class="list-group-item list-group-item-action">
                    <i class="bi bi-gear me-2"></i>Capacités de l'IA
                </a>
                <a href="#search-types" class="list-group-item list-group-item-action">
                    <i class="bi bi-folder me-2"></i>Types de recherche
                </a>
                <a href="#query-examples" class="list-group-item list-group-item-action">
                    <i class="bi bi-chat-quote me-2"></i>Exemples de requêtes
                </a>
                <a href="#date-filters" class="list-group-item list-group-item-action">
                    <i class="bi bi-calendar me-2"></i>Filtres de dates
                </a>
                <a href="#advanced-queries" class="list-group-item list-group-item-action">
                    <i class="bi bi-search me-2"></i>Recherches avancées
                </a>
                <a href="#tips" class="list-group-item list-group-item-action">
                    <i class="bi bi-lightbulb me-2"></i>Conseils d'utilisation
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Content -->
            <div class="documentation-content">

                <!-- Overview Section -->
                <section id="overview" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>Vue d'ensemble
                    </h2>
                    <div class="alert alert-info">
                        <strong>Assistant IA Intelligent</strong> - Votre assistant personnel pour naviguer dans le système d'archives.
                        L'IA comprend le langage naturel français et peut rechercher dans tous les types de documents.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-check-circle me-2"></i>Ce que l'IA peut faire
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Rechercher par mots-clés</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Filtrer par dates et périodes</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Trouver des auteurs spécifiques</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Compter les éléments</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Lister les résultats</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Recherches complexes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Limitations
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-x text-danger me-2"></i>Ne peut pas modifier les données</li>
                                        <li><i class="bi bi-x text-danger me-2"></i>Ne peut pas créer de documents</li>
                                        <li><i class="bi bi-x text-danger me-2"></i>Accès lecture seule</li>
                                        <li><i class="bi bi-info text-info me-2"></i>Limité aux données existantes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Capabilities Section -->
                <section id="capabilities" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-gear me-2"></i>Capacités de l'IA
                    </h2>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <i class="bi bi-search me-2"></i>Recherche intelligente
                                </div>
                                <div class="card-body">
                                    <p>L'IA comprend vos intentions et trouve les documents pertinents même avec des requêtes approximatives.</p>
                                    <small class="text-muted">Exemple: "documents de Martin l'année dernière"</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-funnel me-2"></i>Filtrage avancé
                                </div>
                                <div class="card-body">
                                    <p>Combine plusieurs critères automatiquement : dates, auteurs, types, conteneurs, etc.</p>
                                    <small class="text-muted">Exemple: "mails urgents reçus cette semaine"</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-calendar me-2"></i>Gestion temporelle
                                </div>
                                <div class="card-body">
                                    <p>Comprend les expressions de temps naturelles et les convertit en filtres précis.</p>
                                    <small class="text-muted">Exemple: "hier", "cette année", "janvier 2024"</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Search Types Section -->
                <section id="search-types" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-folder me-2"></i>Types de recherche disponibles
                    </h2>

                    <div class="accordion" id="searchTypesAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#records">
                                    <i class="bi bi-folder text-primary me-2"></i>
                                    <strong>Documents/Archives (Records)</strong>
                                </button>
                            </h2>
                            <div id="records" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> Archives et documents officiels du système.</p>
                                    <p><strong>Critères de recherche:</strong></p>
                                    <ul>
                                        <li><strong>Auteurs:</strong> Nom, prénom des créateurs</li>
                                        <li><strong>Activités:</strong> Type d'activité associée</li>
                                        <li><strong>Conteneurs:</strong> Boîtes, étagères de stockage</li>
                                        <li><strong>Termes:</strong> Mots-clés indexés</li>
                                        <li><strong>Statuts:</strong> État du document</li>
                                        <li><strong>Dates:</strong> Date de début, fin, exacte</li>
                                    </ul>
                                    <div class="alert alert-light">
                                        <strong>Exemples:</strong>
                                        <br>• "documents de Martin en 2024"
                                        <br>• "archives dans le conteneur A123"
                                        <br>• "dossiers avec terme juridique"
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mails">
                                    <i class="bi bi-envelope text-success me-2"></i>
                                    <strong>Courriers (Mails)</strong>
                                </button>
                            </h2>
                            <div id="mails" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> Correspondances et emails du système.</p>
                                    <p><strong>Critères de recherche:</strong></p>
                                    <ul>
                                        <li><strong>Expéditeurs/Destinataires:</strong> Noms des correspondants</li>
                                        <li><strong>Priorité:</strong> Urgent, normal, faible</li>
                                        <li><strong>Type:</strong> Entrant, sortant</li>
                                        <li><strong>Typologie:</strong> Administrative, technique</li>
                                        <li><strong>Pièces jointes:</strong> Contenu des fichiers joints</li>
                                        <li><strong>Dates:</strong> Date de réception, d'envoi</li>
                                    </ul>
                                    <div class="alert alert-light">
                                        <strong>Exemples:</strong>
                                        <br>• "mails urgents reçus hier"
                                        <br>• "courriers de type administratif"
                                        <br>• "emails avec pièces jointes PDF"
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#communications">
                                    <i class="bi bi-chat-dots text-info me-2"></i>
                                    <strong>Communications</strong>
                                </button>
                            </h2>
                            <div id="communications" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> Échanges et transactions entre utilisateurs.</p>
                                    <p><strong>Critères de recherche:</strong></p>
                                    <ul>
                                        <li><strong>Opérateurs:</strong> Noms des agents responsables</li>
                                        <li><strong>Utilisateurs:</strong> Demandeurs ou bénéficiaires</li>
                                        <li><strong>Statuts:</strong> En cours, terminé, annulé</li>
                                        <li><strong>Organisations:</strong> Services impliqués</li>
                                        <li><strong>Dates:</strong> Dates de retour prévues/effectives</li>
                                    </ul>
                                    <div class="alert alert-light">
                                        <strong>Exemples:</strong>
                                        <br>• "communications en cours par Dupont"
                                        <br>• "retours prévus pour janvier 2025"
                                        <br>• "échanges terminés cette semaine"
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#slips">
                                    <i class="bi bi-arrow-left-right text-warning me-2"></i>
                                    <strong>Transferts/Bordereaux (Slips)</strong>
                                </button>
                            </h2>
                            <div id="slips" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> Bordereaux de transfert et mouvements de documents.</p>
                                    <p><strong>Critères de recherche:</strong></p>
                                    <ul>
                                        <li><strong>Agents:</strong> Responsables du transfert</li>
                                        <li><strong>Utilisateurs:</strong> Initiateurs du transfert</li>
                                        <li><strong>Statuts:</strong> Reçu, approuvé, intégré</li>
                                        <li><strong>Conteneurs:</strong> Destination du transfert</li>
                                        <li><strong>Dates:</strong> Réception, approbation, intégration</li>
                                    </ul>
                                    <div class="alert alert-light">
                                        <strong>Exemples:</strong>
                                        <br>• "bordereaux approuvés par Admin"
                                        <br>• "transferts intégrés cette semaine"
                                        <br>• "bordereaux en attente"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Query Examples Section -->
                <section id="query-examples" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-chat-quote me-2"></i>Exemples de requêtes
                    </h2>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h5 class="text-success">
                                <i class="bi bi-check-circle me-2"></i>Requêtes simples
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <code class="text-primary">"Combien d'auteurs ?"</code>
                                        <p class="small text-muted mb-0">Compte le nombre total d'auteurs dans le système</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Derniers documents"</code>
                                        <p class="small text-muted mb-0">Affiche les documents les plus récents</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Documents urgents"</code>
                                        <p class="small text-muted mb-0">Trouve les documents marqués comme urgents</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h5 class="text-info">
                                <i class="bi bi-funnel me-2"></i>Requêtes avec filtres
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <code class="text-primary">"Documents de Martin en 2024"</code>
                                        <p class="small text-muted mb-0">Filtre par auteur et année</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Mails urgents reçus hier"</code>
                                        <p class="small text-muted mb-0">Combine priorité et date</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Archives dans conteneur A123"</code>
                                        <p class="small text-muted mb-0">Recherche par localisation</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h5 class="text-warning">
                                <i class="bi bi-calendar me-2"></i>Requêtes temporelles
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <code class="text-primary">"Documents de cette année"</code>
                                        <p class="small text-muted mb-0">Année en cours</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Mails de janvier 2024"</code>
                                        <p class="small text-muted mb-0">Mois et année spécifiques</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Communications de la semaine passée"</code>
                                        <p class="small text-muted mb-0">Période relative</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h5 class="text-danger">
                                <i class="bi bi-gear me-2"></i>Requêtes complexes
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <code class="text-primary">"Documents urgents de Martin avec terme juridique en 2024"</code>
                                        <p class="small text-muted mb-0">Critères multiples</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Bordereaux approuvés entre janvier et mars"</code>
                                        <p class="small text-muted mb-0">Plage de dates</p>
                                    </div>
                                    <div class="mb-3">
                                        <code class="text-primary">"Communications en cours par équipe administrative"</code>
                                        <p class="small text-muted mb-0">Statut et organisation</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Date Filters Section -->
                <section id="date-filters" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-calendar me-2"></i>Filtres de dates intelligents
                    </h2>

                    <div class="alert alert-info">
                        <strong>L'IA comprend automatiquement les expressions temporelles</strong> et les convertit en filtres précis.
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-clock me-2"></i>Expressions relatives
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><code>"aujourd'hui"</code> → Date actuelle</li>
                                        <li><code>"hier"</code> → Jour précédent</li>
                                        <li><code>"cette semaine"</code> → Semaine courante</li>
                                        <li><code>"ce mois"</code> → Mois en cours</li>
                                        <li><code>"cette année"</code> → Année 2025</li>
                                        <li><code>"l'année dernière"</code> → Année 2024</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <i class="bi bi-calendar-month me-2"></i>Mois et années
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><code>"janvier"</code> → Janvier 2025</li>
                                        <li><code>"février 2024"</code> → Février 2024</li>
                                        <li><code>"2023"</code> → Toute l'année 2023</li>
                                        <li><code>"mars à mai"</code> → Trimestre</li>
                                        <li><code>"premier trimestre"</code> → Jan-Mar</li>
                                        <li><code>"dernier trimestre"</code> → Oct-Déc</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-calendar-range me-2"></i>Plages de dates
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><code>"du 1 janvier au 31 mars"</code></li>
                                        <li><code>"entre 2023 et 2024"</code></li>
                                        <li><code>"depuis janvier"</code></li>
                                        <li><code>"avant 2024"</code></li>
                                        <li><code>"après mars 2024"</code></li>
                                        <li><code>"derniers 6 mois"</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Advanced Queries Section -->
                <section id="advanced-queries" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-search me-2"></i>Recherches avancées
                    </h2>

                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-lightbulb me-2"></i>Combinaisons de critères
                                </div>
                                <div class="card-body">
                                    <p>L'IA peut combiner automatiquement plusieurs critères dans une seule requête :</p>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Requête</th>
                                                    <th>Critères détectés</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>"Documents urgents de Martin créés en janvier dans conteneur A123"</code></td>
                                                    <td>Priorité + Auteur + Date + Conteneur</td>
                                                    <td>Filter</td>
                                                </tr>
                                                <tr>
                                                    <td><code>"Mails administratifs reçus cette semaine par l'équipe technique"</code></td>
                                                    <td>Type + Date + Organisation</td>
                                                    <td>Filter</td>
                                                </tr>
                                                <tr>
                                                    <td><code>"Communications terminées avec retour effectif en 2024"</code></td>
                                                    <td>Statut + Date de retour + Année</td>
                                                    <td>Filter</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-funnel me-2"></i>Logique de filtrage
                                </div>
                                <div class="card-body">
                                    <h6>L'IA applique automatiquement :</h6>
                                    <ul>
                                        <li><strong>ET logique</strong> entre différents types de critères</li>
                                        <li><strong>OU logique</strong> pour les variantes d'un même critère</li>
                                        <li><strong>Recherche floue</strong> pour les noms et termes</li>
                                        <li><strong>Normalisation</strong> des dates et formats</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-cpu me-2"></i>Intelligence contextuelle
                                </div>
                                <div class="card-body">
                                    <h6>L'IA s'adapte au contexte :</h6>
                                    <ul>
                                        <li><strong>Type de document</strong> sélectionné (Records, Mails, etc.)</li>
                                        <li><strong>Champs disponibles</strong> pour chaque type</li>
                                        <li><strong>Relations</strong> entre tables (auteurs, conteneurs)</li>
                                        <li><strong>Synonymes</strong> et variantes de termes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Tips Section -->
                <section id="tips" class="mb-5">
                    <h2 class="text-primary mb-3">
                        <i class="bi bi-lightbulb me-2"></i>Conseils d'utilisation
                    </h2>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <i class="bi bi-check-circle me-2"></i>Bonnes pratiques
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="bi bi-arrow-right text-success me-2"></i>
                                            <strong>Soyez naturel :</strong> Formulez vos questions comme vous parleriez à un collègue
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-arrow-right text-success me-2"></i>
                                            <strong>Soyez spécifique :</strong> Plus vous donnez de détails, plus les résultats seront précis
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-arrow-right text-success me-2"></i>
                                            <strong>Utilisez des synonymes :</strong> L'IA comprend différentes façons de dire la même chose
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-arrow-right text-success me-2"></i>
                                            <strong>Testez différentes approches :</strong> Si une requête ne fonctionne pas, reformulez
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Erreurs communes
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="bi bi-x text-danger me-2"></i>
                                            <strong>Évitez :</strong> Les requêtes trop vagues ("montre-moi tout")
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-x text-danger me-2"></i>
                                            <strong>Évitez :</strong> Les abréviations non-standard
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-x text-danger me-2"></i>
                                            <strong>Évitez :</strong> Les formats de date ambigus
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-x text-danger me-2"></i>
                                            <strong>Évitez :</strong> Les noms de champs techniques
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-info-circle me-2"></i>Optimisation des recherches
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-primary">Pour compter rapidement :</h6>
                                    <ul class="small">
                                        <li>Utilisez "combien" dans votre question</li>
                                        <li>Ajoutez des critères de filtre</li>
                                        <li>Exemple: "Combien de mails urgents ?"</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-primary">Pour voir des listes :</h6>
                                    <ul class="small">
                                        <li>Évitez le mot "combien"</li>
                                        <li>Utilisez "montre", "liste", "trouve"</li>
                                        <li>Exemple: "Liste des documents de 2024"</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-primary">Pour des détails :</h6>
                                    <ul class="small">
                                        <li>Mentionnez un ID spécifique</li>
                                        <li>Utilisez "détails", "informations"</li>
                                        <li>Exemple: "Détails du document #123"</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <!-- Quick Access FAB -->
    <div class="position-fixed bottom-0 end-0 p-3">
        <a href="{{ route('ai-search.index') }}" class="btn btn-primary btn-lg rounded-circle shadow" title="Essayer l'IA">
            <i class="bi bi-robot"></i>
        </a>
    </div>
</div>

@endsection

@section('styles')
<style>
.documentation-content {
    max-height: 80vh;
    overflow-y: auto;
}

.list-group-item-action {
    border: none;
    border-radius: 8px;
    margin-bottom: 2px;
}

.list-group-item-action:hover {
    background-color: #f8f9fa;
}

.list-group-item-action.active {
    background-color: #007bff;
    border-color: #007bff;
}

code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    color: #007bff;
    border: 1px solid #e9ecef;
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

section {
    scroll-margin-top: 100px;
}

.table code {
    background-color: #e3f2fd;
    border-color: #2196f3;
    font-size: 0.85em;
}

.alert {
    border-radius: 8px;
}

.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
}

.sticky-top {
    z-index: 1020;
}

@media (max-width: 768px) {
    .documentation-content {
        max-height: none;
    }

    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('.list-group-item-action').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Update active state
                document.querySelectorAll('.list-group-item-action').forEach(function(item) {
                    item.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });

    // Highlight current section on scroll
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                document.querySelectorAll('.list-group-item-action').forEach(function(item) {
                    item.classList.remove('active');
                });
                const correspondingLink = document.querySelector(`[href="#${id}"]`);
                if (correspondingLink) {
                    correspondingLink.classList.add('active');
                }
            }
        });
    }, {
        rootMargin: '-50px 0px -50px 0px'
    });

    // Observe all sections
    document.querySelectorAll('section').forEach(function(section) {
        observer.observe(section);
    });
});
</script>
@endsection