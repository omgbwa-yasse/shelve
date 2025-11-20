-- ============================================
-- TABLES ÉDITEURS
-- ============================================

-- Table des éditeurs
CREATE TABLE editeurs (
    editeur_id INT PRIMARY KEY AUTO_INCREMENT,
    nom_editeur VARCHAR(255) NOT NULL,
    ville VARCHAR(255),
    pays VARCHAR(100),
    adresse TEXT,
    site_web VARCHAR(255),
    date_creation DATE,
    note TEXT,

    INDEX idx_nom(nom_editeur)
);

-- ============================================
-- TABLES COLLECTIONS - ZONE 6 (hiérarchique)
-- ============================================

-- Table des collections avec hiérarchie
CREATE TABLE collections (
    collection_id INT PRIMARY KEY AUTO_INCREMENT,
    editeur_id INT, -- Collection rattachée à un éditeur
    parent_id INT, -- Pour les sous-collections

    titre_collection VARCHAR(255) NOT NULL,
    titre_section VARCHAR(255), -- Sous-titre de la collection
    issn VARCHAR(20),

    -- Hiérarchie et ordre
    niveau INT DEFAULT 1, -- 1=collection principale, 2=sous-collection, etc.
    ordre_affichage INT DEFAULT 0,

    note TEXT,

    FOREIGN KEY (editeur_id) REFERENCES editeurs(editeur_id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES collections(collection_id) ON DELETE SET NULL,

    INDEX idx_titre(titre_collection),
    INDEX idx_issn(issn),
    INDEX idx_parent(parent_id),
    INDEX idx_editeur(editeur_id)
);

-- ============================================
-- TABLES PRINCIPALES - NOTICES BIBLIOGRAPHIQUES
-- ============================================

-- Table des notices bibliographiques (monographies)
CREATE TABLE notices (
    notice_id INT PRIMARY KEY AUTO_INCREMENT,
    typdoc VARCHAR(2) DEFAULT 'a', -- Type de document (a=monographie, s=périodique, etc.)
    statut CHAR(1) DEFAULT 'x', -- Statut (x=complet, y=provisoire, r=rétro)

    -- ZONE 0 : Forme du contenu et type de médiation
    forme_contenu VARCHAR(100), -- Ex: "Texte"
    type_mediation VARCHAR(100), -- Ex: "visuel : immédiat"

    -- ZONE 1 : Titre et mention de responsabilité
    titre_propre TEXT NOT NULL,
    titre_parallele TEXT,
    complement_titre TEXT,
    titre_cle TEXT,

    -- ZONE 2 : Édition
    mention_edition VARCHAR(255),
    mention_resp_edition TEXT,

    -- ZONE 3 : Spécifique
    zone_specifique TEXT, -- Pour cartes, musique, etc.

    -- ZONE 4 : Adresse bibliographique (MODIFIÉE)
    annee_publication VARCHAR(10), -- Année principale
    date_publication VARCHAR(50), -- Date complète si disponible
    date_depot_legal VARCHAR(50),
    date_copyright VARCHAR(50),

    -- ZONE 5 : Collation
    importance_materielle VARCHAR(255), -- Ex: "1 vol. (197 p.)"
    autre_materiel TEXT, -- Illustrations, etc.
    format_dimensions VARCHAR(50), -- Ex: "17 cm"
    materiel_accompagnement TEXT,

    -- ZONE 7 : Notes
    notes_generales TEXT,
    notes_contenu TEXT,
    notes_bibliographie TEXT,
    notes_resume TEXT,
    notes_public_destine TEXT,

    -- ZONE 8 : Numéros d'identification
    isbn VARCHAR(20),
    isbn_errone VARCHAR(20),
    ean VARCHAR(20),
    issn VARCHAR(20), -- Un livre peut avoir un ISSN (série continue)
    numero_editeur VARCHAR(100), -- Numéro propre de l'éditeur
    autre_numero TEXT,
    prix VARCHAR(50),
    disponibilite TEXT,

    -- Métadonnées
    code_langue VARCHAR(10),
    code_pays VARCHAR(10),

    -- Gestion
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    catalogueur VARCHAR(100),
    source_notice VARCHAR(50), -- Ex: "SUDOC", "BNF"
    ppn VARCHAR(20), -- Numéro PPN du SUDOC

    INDEX idx_titre(titre_propre(100)),
    INDEX idx_isbn(isbn),
    INDEX idx_ean(ean),
    INDEX idx_issn(issn),
    INDEX idx_numero_editeur(numero_editeur),
    INDEX idx_annee(annee_publication),
    INDEX idx_ppn(ppn)
);



-- ============================================
-- TABLES CLASSIFICATION
-- ============================================

-- Table des classifications (Dewey, CDU, etc.)
CREATE TABLE classifications (
    classification_id INT PRIMARY KEY AUTO_INCREMENT,
    type_classification VARCHAR(50) NOT NULL, -- Ex: "Dewey", "CDU", "LC"
    code_classification VARCHAR(100) NOT NULL,
    libelle TEXT NOT NULL,
    parent_id INT, -- Pour hiérarchie
    niveau INT DEFAULT 1,

    FOREIGN KEY (parent_id) REFERENCES classifications(classification_id) ON DELETE SET NULL,

    INDEX idx_code(code_classification),
    INDEX idx_type(type_classification),
    INDEX idx_parent(parent_id)
);

-- Liaison notices <-> classifications
CREATE TABLE notices_classifications (
    notice_id INT NOT NULL,
    classification_id INT NOT NULL,
    ordre INT DEFAULT 1,

    PRIMARY KEY (notice_id, classification_id),
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (classification_id) REFERENCES classifications(classification_id) ON DELETE CASCADE,

    INDEX idx_notice(notice_id),
    INDEX idx_classification(classification_id)
);

-- ============================================
-- TABLES MOTS-CLÉS LIBRES
-- ============================================

-- Table des mots-clés libres
CREATE TABLE mots_cles (
    mot_cle_id INT PRIMARY KEY AUTO_INCREMENT,
    terme VARCHAR(255) NOT NULL UNIQUE,
    langue VARCHAR(10) DEFAULT 'fre',
    note TEXT,

    INDEX idx_terme(terme)
);

-- Liaison notices <-> mots-clés
CREATE TABLE notices_mots_cles (
    notice_id INT NOT NULL,
    mot_cle_id INT NOT NULL,
    ordre INT DEFAULT 1,

    PRIMARY KEY (notice_id, mot_cle_id),
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (mot_cle_id) REFERENCES mots_cles(mot_cle_id) ON DELETE CASCADE,

    INDEX idx_notice(notice_id),
    INDEX idx_mot_cle(mot_cle_id)
);

-- ============================================
-- TABLES THÉSAURUS
-- ============================================




-- Liaison notices <-> thésaurus
CREATE TABLE notices_terms (
    notice_id INT NOT NULL,
    terms_id INT NOT NULL,
    ordre INT DEFAULT 1,

    PRIMARY KEY (notice_id, terms_id),
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (terms_id) REFERENCES thesaurus(terms_id) ON DELETE CASCADE,

    INDEX idx_notice(notice_id),
    INDEX idx_thesaurus(thesaurus_id)
);

-- ============================================
-- LIAISON NOTICES <-> ÉDITEURS (plusieurs éditeurs possibles)
-- ============================================

-- Un livre peut avoir plusieurs éditeurs avec leur lieu
CREATE TABLE notices_editeurs (
    notice_editeur_id INT PRIMARY KEY AUTO_INCREMENT,
    notice_id INT NOT NULL,
    editeur_id INT NOT NULL,

    lieu_edition VARCHAR(255), -- Lieu spécifique pour cet éditeur
    ordre INT DEFAULT 1, -- Ordre d'apparition (1er éditeur, 2ème éditeur, etc.)
    role_editeur VARCHAR(50), -- Ex: "éditeur commercial", "diffuseur", "distributeur"

    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (editeur_id) REFERENCES editeurs(editeur_id) ON DELETE CASCADE,

    INDEX idx_notice(notice_id),
    INDEX idx_editeur(editeur_id),
    INDEX idx_ordre(ordre)
);

-- ============================================
-- TABLES AUTORITÉS
-- ============================================

-- Table des auteurs (personnes physiques)
CREATE TABLE auteurs (
    auteur_id INT PRIMARY KEY AUTO_INCREMENT,
    type_auteur ENUM('personne', 'collectivite', 'congres') DEFAULT 'personne',
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255),
    dates VARCHAR(100), -- Ex: "1909-1943"
    forme_rejetee TEXT, -- Autres formes du nom
    pays VARCHAR(100),
    langue VARCHAR(50),
    note_biographique TEXT,
    ppn_autorite VARCHAR(20), -- Lien vers autorité SUDOC
    isni VARCHAR(50), -- Identifiant international

    INDEX idx_nom(nom),
    INDEX idx_ppn_autorite(ppn_autorite)
);

-- Table des collectivités
CREATE TABLE collectivites (
    collectivite_id INT PRIMARY KEY AUTO_INCREMENT,
    nom TEXT NOT NULL,
    subdivision TEXT,
    lieu VARCHAR(255),
    dates VARCHAR(100),
    ppn_autorite VARCHAR(20),

    INDEX idx_nom(nom(100))
);

-- ============================================
-- TABLES DE LIAISON - ZONE 1
-- ============================================

-- Liaison notices <-> auteurs (responsabilités)
CREATE TABLE responsabilite (
    responsabilite_id INT PRIMARY KEY AUTO_INCREMENT,
    notice_id INT NOT NULL,
    auteur_id INT,
    collectivite_id INT,
    type_responsabilite VARCHAR(10), -- Ex: "070" (auteur), "730" (traducteur)
    fonction VARCHAR(100), -- Ex: "Auteur", "Traducteur", "Préfacier"
    ordre INT DEFAULT 1,

    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES auteurs(auteur_id) ON DELETE SET NULL,
    FOREIGN KEY (collectivite_id) REFERENCES collectivites(collectivite_id) ON DELETE SET NULL,

    INDEX idx_notice(notice_id),
    INDEX idx_auteur(auteur_id),
    INDEX idx_type(type_responsabilite)
);

-- ============================================
-- LIAISON NOTICES <-> COLLECTIONS
-- ============================================

-- Liaison notices <-> collections
CREATE TABLE notices_collections (
    notice_id INT NOT NULL,
    collection_id INT NOT NULL,
    numero_collection VARCHAR(50), -- Numéro dans la collection

    PRIMARY KEY (notice_id, collection_id),
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (collection_id) REFERENCES collections(collection_id) ON DELETE CASCADE
);

-- ============================================
-- INDEXATION MATIÈRES - ZONE 6XX
-- ============================================

-- Table des catégories (inspiré RAMEAU)
CREATE TABLE categories (
    categorie_id INT PRIMARY KEY AUTO_INCREMENT,
    libelle VARCHAR(255) NOT NULL,
    langue VARCHAR(10) DEFAULT 'fre',
    note_application TEXT,
    ppn_rameau VARCHAR(20),

    INDEX idx_libelle(libelle)
);

-- Liaison notices <-> catégories
CREATE TABLE notices_categories (
    notice_id INT NOT NULL,
    categorie_id INT NOT NULL,
    ordre INT DEFAULT 1,

    PRIMARY KEY (notice_id, categorie_id),
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(categorie_id) ON DELETE CASCADE
);

-- ============================================
-- GESTION DES EXEMPLAIRES
-- ============================================

-- Table des bibliothèques/sites
CREATE TABLE bibliotheques (
    bibliotheque_id INT PRIMARY KEY AUTO_INCREMENT,
    code_rcr VARCHAR(20) UNIQUE, -- Répertoire des Centres de Ressources
    nom VARCHAR(255) NOT NULL,
    code_etab VARCHAR(50), -- ILN (Internal Library Number)
    adresse TEXT,
    email VARCHAR(255),
    telephone VARCHAR(50),

    INDEX idx_rcr(code_rcr)
);

-- Table des localisations (rayonnages)
CREATE TABLE localisations (
    localisation_id INT PRIMARY KEY AUTO_INCREMENT,
    bibliotheque_id INT NOT NULL,
    code_localisation VARCHAR(50) NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    visible_opac BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (bibliotheque_id) REFERENCES bibliotheques(bibliotheque_id) ON DELETE CASCADE,

    INDEX idx_code(code_localisation)
);

-- Table des sections
CREATE TABLE sections (
    section_id INT PRIMARY KEY AUTO_INCREMENT,
    code_section VARCHAR(50) NOT NULL,
    libelle VARCHAR(255) NOT NULL,

    INDEX idx_code(code_section)
);

-- Table des supports
CREATE TABLE supports (
    support_id INT PRIMARY KEY AUTO_INCREMENT,
    code_support VARCHAR(50) NOT NULL,
    libelle VARCHAR(255) NOT NULL, -- Ex: "Livre", "DVD", "CD-ROM"

    INDEX idx_code(code_support)
);

-- Table des statuts d'exemplaire
CREATE TABLE statuts_exemplaire (
    statut_id INT PRIMARY KEY AUTO_INCREMENT,
    code_statut VARCHAR(50) NOT NULL,
    libelle VARCHAR(255) NOT NULL, -- Ex: "Disponible", "En prêt", "Perdu"
    pret_autorise BOOLEAN DEFAULT TRUE,
    visible_opac BOOLEAN DEFAULT TRUE,

    INDEX idx_code(code_statut)
);

-- Table principale des exemplaires
CREATE TABLE exemplaires (
    exemplaire_id INT PRIMARY KEY AUTO_INCREMENT,
    epn VARCHAR(50) UNIQUE, -- Numéro d'exemplaire (EPN)
    notice_id INT NOT NULL,

    -- Localisation
    bibliotheque_id INT NOT NULL,
    localisation_id INT,
    section_id INT,

    -- Identification
    cote VARCHAR(255),
    support_id INT,
    statut_id INT NOT NULL,

    -- Codes-barres
    code_barre VARCHAR(50) UNIQUE,

    -- Informations complémentaires
    note_exemplaire TEXT,
    note_publique TEXT,
    prix_acquisition DECIMAL(10,2),
    date_acquisition DATE,
    date_creation_ex DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Gestion du prêt
    empruntable BOOLEAN DEFAULT TRUE,
    consultable_sur_place BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (bibliotheque_id) REFERENCES bibliotheques(bibliotheque_id),
    FOREIGN KEY (localisation_id) REFERENCES localisations(localisation_id),
    FOREIGN KEY (section_id) REFERENCES sections(section_id),
    FOREIGN KEY (support_id) REFERENCES supports(support_id),
    FOREIGN KEY (statut_id) REFERENCES statuts_exemplaire(statut_id),

    INDEX idx_notice(notice_id),
    INDEX idx_cote(cote),
    INDEX idx_code_barre(code_barre),
    INDEX idx_statut(statut_id)
);

