package com.shelve.seed;

import java.util.List;
import java.util.Map;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Alignement Constellio — correction de la configuration des domaines de valeurs,
 * des types de dossier/document et des métadonnées. Idempotent, rejouable.
 *
 * Corrections appliquées :
 *  1. Deux domaines de valeurs distincts : `FOLDER_TYPES` (Types de dossiers,
 *     is_container=1) et `DOCUMENT_TYPES` (Types de documents, is_container=0),
 *     chacun lié à son schéma via `linked_schema_id`.
 *  2. Chaque `record_types` est rattaché au bon domaine (`reference_list_id`).
 *  3. Chaque type de dossier/document devient une **valeur** de son domaine
 *     (`reference_values`) — synchronisation idempotente type ↔ valeur.
 *  4. Les métadonnées de type `select` sans domaine sont rattachées à un domaine
 *     dédié (les options JSON deviennent des valeurs).
 *  5. Groupes (onglets) de métadonnées posés sur les profils (`group`).
 *  6. Les types de test non utilisés (aucune notice) sont désactivés.
 *
 * Utilise JdbcTemplate pour les colonnes non couvertes par les entités JPA
 * (reference_list_id, is_active, group, …).
 */
@Component
public class ConstellioAlignmentSeeder {
  private static final Logger log = LoggerFactory.getLogger(ConstellioAlignmentSeeder.class);

  private final JdbcTemplate jdbc;

  public ConstellioAlignmentSeeder(JdbcTemplate jdbc) {
    this.jdbc = jdbc;
  }

  @Transactional
  public void run() {
    this.ensureDomains();
    this.ensureSystemDomains();
    this.attachTypesToDomains();
    this.syncDomainValues();
    this.attachSelectMetadataToDomains();
    this.setMetadataGroups();
    this.deactivateUnusedTestTypes();
    this.ensureDeclassementStatuses();
    log.info("Alignement Constellio : domaines de valeurs, types et métadonnées corrigés.");
  }

  /** Statuts de déclassement de base (workflow D07). */
  private void ensureDeclassementStatuses() {
    List<String> statuses =
        List.of(
            "Brouillon",
            "Demande d'approbation soumise",
            "Approuvé",
            "Validé",
            "Traité",
            "Rejeté");
    for (String name : statuses) {
      Long existing =
          this.jdbc.query(
              "select id from declassement_statuses where name = ?",
              rs -> rs.next() ? rs.getLong(1) : null,
              name);
      if (existing == null) {
        this.jdbc.update(
            "insert into declassement_statuses (name, created_at, updated_at) values (?, now(), now())",
            name);
      }
    }
    log.info("  Statuts de déclassement vérifiés ({} attendus).", statuses.size());
  }

  /* ------------------------------------------------------------------ */
  /*  1. Domaines de valeurs (FOLDER_TYPES / DOCUMENT_TYPES)             */
  /* ------------------------------------------------------------------ */

  private void ensureDomains() {
    // Schéma (record_type) lié à chaque domaine : FONDS pour les dossiers,
    // PAPER_RECORD pour les documents.
    Long folderSchemaId = this.typeId("FONDS");
    Long documentSchemaId = this.typeId("PAPER_RECORD");
    if (folderSchemaId == null || documentSchemaId == null) {
      log.warn("Alignement Constellio : FONDS ou PAPER_RECORD introuvable — linked_schema_id non posé.");
    }

    Long folderDomain = this.domainId("FOLDER_TYPES");
    if (folderDomain == null) {
      this.jdbc.update(
          "insert into reference_lists (name, code, description, linked_schema_id, active, created_by, created_at, updated_at) values (?, ?, ?, ?, 1, 1, now(), now())",
          "Types de dossiers", "FOLDER_TYPES", "Domaines de valeurs des types de dossiers", folderSchemaId);
      folderDomain = this.domainId("FOLDER_TYPES");
    } else if (folderSchemaId != null) {
      this.jdbc.update("update reference_lists set linked_schema_id = ? where id = ?", folderSchemaId, folderDomain);
    }

    Long documentDomain = this.domainId("DOCUMENT_TYPES");
    if (documentDomain == null) {
      this.jdbc.update(
          "insert into reference_lists (name, code, description, linked_schema_id, active, created_by, created_at, updated_at) values (?, ?, ?, ?, 1, 1, now(), now())",
          "Types de documents", "DOCUMENT_TYPES", "Domaines de valeurs des types de documents", documentSchemaId);
      documentDomain = this.domainId("DOCUMENT_TYPES");
    } else if (documentSchemaId != null) {
      this.jdbc.update("update reference_lists set linked_schema_id = ? where id = ?", documentSchemaId, documentDomain);
    }

    log.info("  1. Domaines : FOLDER_TYPES={}, DOCUMENT_TYPES={}", folderDomain, documentDomain);
  }

  /* ------------------------------------------------------------------ */
  /*  1bis. Domaines de valeurs SYSTÈME Constellio                       */
  /* ------------------------------------------------------------------ */

  /**
   * Crée les domaines de valeurs système Constellio manquants (is_system=1) et
   * leurs valeurs par défaut, avec le schéma lié (`linked_schema_id`) quand un
   * RecordType correspondant existe. Idempotent.
   *
   * Codes : CONTAINER_TYPES, LOCATION_TYPES, TASK_TYPES, TASK_STATUS,
   * SUPPORT_TYPES, YEAR_TYPES, PRIORITY_TYPES (+ DOCUMENT_TYPES/FOLDER_TYPES
   * déjà garantis par ensureDomains, marqués système ici).
   */
  private void ensureSystemDomains() {
    // [code, name, description, schemaCode?, values...]
    List<Object[]> domains =
        List.of(
            new Object[] {"CONTAINER_TYPES", "Types de contenants", "Types de contenants physiques (boîtes, cartons, dossiers suspendus, ...)", "CONTAINER", new String[] {"Boîte", "Carton", "Classeur", "Dossier suspendu"}},
            new Object[] {"LOCATION_TYPES", "Types d'emplacements", "Types de lieux de conservation", "LOCATION", new String[] {"Local", "Rayonnage", "Salle", "Dépôt"}},
            new Object[] {"TASK_TYPES", "Types de tâches", "Typologies des tâches de workflow", "TASK", new String[] {"Tâche simple", "Approbation", "Validation", "Relance"}},
            new Object[] {"TASK_STATUS", "Statut d'une tâche", "Statuts possibles d'une tâche (en attente, en cours, complétée, fermée)", null, new String[] {"En attente", "En cours", "Complétée", "Fermée"}},
            new Object[] {"SUPPORT_TYPES", "Types de supports", "Supports documentaires (papier, numérique, ...)", "SUPPORT", null},
            new Object[] {"YEAR_TYPES", "Types d'années", "Types d'années de gestion des documents", null, new String[] {"Année civile", "Année budgétaire", "Année scolaire"}},
            new Object[] {"PRIORITY_TYPES", "Types de priorités", "Niveaux de priorité des tâches", null, new String[] {"Basse", "Normale", "Haute", "Critique"}});

    for (Object[] d : domains) {
      String code = (String) d[0];
      String name = (String) d[1];
      String description = (String) d[2];
      String schemaCode = (String) d[3];
      String[] values = (String[]) d[4];
      Long schemaId = schemaCode != null ? this.typeId(schemaCode) : null;

      Long domainId = this.domainId(code);
      if (domainId == null) {
        this.jdbc.update(
            "insert into reference_lists (name, code, description, is_system, linked_schema_id, active, created_by, created_at, updated_at) values (?, ?, ?, 1, ?, 1, 1, now(), now())",
            name, code, description, schemaId);
        domainId = this.domainId(code);
      } else {
        // Marque système et met à jour le schéma lié si dispo.
        this.jdbc.update("update reference_lists set is_system = 1, linked_schema_id = coalesce(?, linked_schema_id) where id = ?", schemaId, domainId);
      }

      // Valeurs par défaut (idempotent).
      if (values != null) {
        int order = 1;
        for (String value : values) {
          String vCode = this.slug(value);
          Long existing =
              this.jdbc.query(
                  "select id from reference_values where list_id = ? and code = ? and deleted_at is null",
                  rs -> rs.next() ? rs.getLong(1) : null,
                  domainId, vCode);
          if (existing == null) {
            this.jdbc.update(
                "insert into reference_values (list_id, value, code, active, sort_order, created_by, created_at, updated_at) values (?, ?, ?, 1, ?, 1, now(), now())",
                domainId, value, vCode, order);
          }
          order++;
        }
      }
    }

    // Marque DOCUMENT_TYPES et FOLDER_TYPES comme système.
    for (String code : List.of("DOCUMENT_TYPES", "FOLDER_TYPES")) {
      Long domainId = this.domainId(code);
      if (domainId != null) {
        this.jdbc.update("update reference_lists set is_system = 1 where id = ?", domainId);
      }
    }

    // SUPPORT_TYPES : dérive les valeurs des supports existants (record_supports).
    Long supportDomain = this.domainId("SUPPORT_TYPES");
    if (supportDomain != null) {
      List<Map<String, Object>> supports =
          this.jdbc.queryForList(
              "select id, name from record_supports order by id");
      for (Map<String, Object> s : supports) {
        String name = String.valueOf(s.get("name"));
        String code = this.slug(name);
        Long existing =
            this.jdbc.query(
                "select id from reference_values where list_id = ? and code = ? and deleted_at is null",
                rs -> rs.next() ? rs.getLong(1) : null,
                supportDomain, code);
        if (existing == null) {
          this.jdbc.update(
              "insert into reference_values (list_id, value, code, active, sort_order, created_by, created_at, updated_at) values (?, ?, ?, 1, ?, 1, now(), now())",
              supportDomain, name, code, ((Number) s.get("id")).intValue());
        }
      }
    }

    log.info("  1bis. Domaines de valeurs système Constellio garantis (is_system=1).");
  }

  /* ------------------------------------------------------------------ */
  /*  2. Rattachement des types aux domaines                             */
  /* ------------------------------------------------------------------ */

  private void attachTypesToDomains() {
    Long folderDomain = this.domainId("FOLDER_TYPES");
    Long documentDomain = this.domainId("DOCUMENT_TYPES");
    if (folderDomain != null) {
      this.jdbc.update(
          "update record_types set reference_list_id = ? where is_container = 1 and (reference_list_id is null or reference_list_id <> ?)",
          folderDomain, folderDomain);
    }
    if (documentDomain != null) {
      this.jdbc.update(
          "update record_types set reference_list_id = ? where is_container = 0 and (reference_list_id is null or reference_list_id <> ?)",
          documentDomain, documentDomain);
    }
    log.info("  2. Types rattachés à leur domaine (dossiers → FOLDER_TYPES, documents → DOCUMENT_TYPES).");
  }

  /* ------------------------------------------------------------------ */
  /*  3. Chaque type = une valeur de son domaine                         */
  /* ------------------------------------------------------------------ */

  private void syncDomainValues() {
    List<Map<String, Object>> types =
        this.jdbc.queryForList(
            "select rt.id, rt.code, rt.name, rt.is_container from record_types rt where rt.deleted_at is null order by rt.id");
    int created = 0;
    for (Map<String, Object> t : types) {
      Long id = ((Number) t.get("id")).longValue();
      String code = (String) t.get("code");
      String name = (String) t.get("name");
      Object isContainerRaw = t.get("is_container");
      boolean container =
          isContainerRaw instanceof Boolean b
              ? b
              : isContainerRaw instanceof Number n && n.longValue() == 1L;
      Long domain = container ? this.domainId("FOLDER_TYPES") : this.domainId("DOCUMENT_TYPES");
      if (domain == null) continue;
      Long valueId =
          this.jdbc.query(
              "select id from reference_values where list_id = ? and code = ? and deleted_at is null",
              rs -> rs.next() ? rs.getLong(1) : null,
              domain, code);
      if (valueId == null) {
        this.jdbc.update(
            "insert into reference_values (list_id, value, code, description, active, sort_order, created_by, created_at, updated_at) values (?, ?, ?, ?, 1, ?, 1, now(), now())",
            domain, name, code, "Type de " + (container ? "dossier" : "document"), (int) (long) id);
        created++;
      } else {
        this.jdbc.update("update reference_values set value = ? where id = ?", name, valueId);
      }
    }
    log.info("  3. Valeurs de domaines synchronisées ({} créées).", created);
  }

  /* ------------------------------------------------------------------ */
  /*  4. Métadonnées select → domaine dédié                              */
  /* ------------------------------------------------------------------ */

  private void attachSelectMetadataToDomains() {
    // code métadonnée → code domaine + libellé.
    Map<String, String[]> selectDomains =
        Map.of(
            "nature_contrat", new String[] {"NATURE_CONTRAT", "Nature de contrat"},
            "corr_mode_transmission", new String[] {"MODE_TRANSMISSION", "Mode de transmission"},
            "delib_resultat_vote", new String[] {"RESULTAT_VOTE", "Résultat du vote"});

    for (Map.Entry<String, String[]> e : selectDomains.entrySet()) {
      String mdCode = e.getKey();
      String domainCode = e.getValue()[0];
      String domainName = e.getValue()[1];
      Long domainId = this.domainId(domainCode);
      if (domainId == null) {
        this.jdbc.update(
            "insert into reference_lists (name, code, description, active, created_by, created_at, updated_at) values (?, ?, ?, 1, 1, now(), now())",
            domainName, domainCode, "Valeurs de la métadonnée " + mdCode);
        domainId = this.domainId(domainCode);
      }

      // Récupère les options JSON de la métadonnée.
      String optionsJson =
          this.jdbc.query(
              "select options from metadata_definitions where code = ? and deleted_at is null",
              rs -> rs.next() ? rs.getString(1) : null,
              mdCode);
      if (optionsJson != null && domainId != null) {
        this.seedOptionsAsValues(domainId, optionsJson);
        this.jdbc.update("update metadata_definitions set reference_list_id = ? where code = ?", domainId, mdCode);
      }
    }
    log.info("  4. Métadonnées select rattachées à leur domaine de valeurs.");
  }

  private void seedOptionsAsValues(Long domainId, String optionsJson) {
    try {
      com.fasterxml.jackson.databind.JsonNode arr =
          new com.fasterxml.jackson.databind.ObjectMapper().readTree(optionsJson);
      if (!arr.isArray()) return;
      int order = 1;
      for (com.fasterxml.jackson.databind.JsonNode node : arr) {
        String value = node.isValueNode() ? node.asText() : node.toString();
        String code = this.slug(value);
        Long existing =
            this.jdbc.query(
                "select id from reference_values where list_id = ? and code = ? and deleted_at is null",
                rs -> rs.next() ? rs.getLong(1) : null,
                domainId, code);
        if (existing == null) {
          this.jdbc.update(
              "insert into reference_values (list_id, value, code, active, sort_order, created_by, created_at, updated_at) values (?, ?, ?, 1, ?, 1, now(), now())",
              domainId, value, code, order);
        }
        order++;
      }
    } catch (Exception ex) {
      log.warn("Options JSON illisibles pour le domaine {} — ignorées.", domainId, ex);
    }
  }

  /* ------------------------------------------------------------------ */
  /*  5. Groupes (onglets) de métadonnées                                */
  /* ------------------------------------------------------------------ */

  private void setMetadataGroups() {
    // groupement par famille de métadonnées (codes préfixés).
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Description ISAD(G)' where m.is_system = 1");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Courrier' where m.code like 'corr_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Rapport' where m.code like 'rapport_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Procès-verbal' where m.code like 'pv_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Contrat' where m.code like 'contrat_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Facture' where m.code like 'facture_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Arrêté' where m.code like 'arrete_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Délibération' where m.code like 'delib_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Registre' where m.code like 'registre_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Photographie' where m.code like 'photo_%'");
    this.jdbc.update(
        "update record_type_metadata_profiles p join metadata_definitions m on m.id = p.metadata_definition_id "
            + "set p.`group` = 'Dictionnaire' where m.code like 'dico_%'");
    log.info("  5. Groupes (onglets) de métadonnées posés sur les profils.");
  }

  /* ------------------------------------------------------------------ */
  /*  6. Types de test inutilisés désactivés                             */
  /* ------------------------------------------------------------------ */

  private void deactivateUnusedTestTypes() {
    // Types de test (FTYPE-*, TYPE-*, test_*) sans aucune notice → inactifs.
    int n =
        this.jdbc.update(
            "update record_types rt set rt.is_active = 0 where "
                + "(rt.code like 'FTYPE-%' or rt.code like 'TYPE-%' or rt.code like 'test_%' or rt.code = 'test_doc') "
                + "and rt.is_active = 1 and not exists (select 1 from records r where r.type_id = rt.id and r.deleted_at is null)");
    log.info("  6. {} types de test inutilisés désactivés.", n);
  }

  /* ------------------------------------------------------------------ */
  /*  Helpers                                                            */
  /* ------------------------------------------------------------------ */

  private Long domainId(String code) {
    return this.jdbc.query(
        "select id from reference_lists where code = ? and deleted_at is null",
        rs -> rs.next() ? rs.getLong(1) : null,
        code);
  }

  private Long typeId(String code) {
    return this.jdbc.query(
        "select id from record_types where code = ? and deleted_at is null",
        rs -> rs.next() ? rs.getLong(1) : null,
        code);
  }

  private static String slug(String value) {
    String s = value.trim().toLowerCase();
    s = s.replaceAll("[àáâãä]", "a").replaceAll("[èéêë]", "e").replaceAll("[ìíîï]", "i")
        .replaceAll("[òóôõö]", "o").replaceAll("[ùúûü]", "u").replaceAll("[ç]", "c")
        .replaceAll("[^a-z0-9]+", "_").replaceAll("(^_|_$)", "");
    return s.isEmpty() ? "V" + Math.abs(value.hashCode()) : s;
  }
}
