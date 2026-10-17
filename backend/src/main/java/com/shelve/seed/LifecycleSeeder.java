package com.shelve.seed;

import java.util.List;
import java.util.Map;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Cycle de vie des documents — jeu de base DUA/DUL (alignement Constellio).
 *
 * - **Communicabilités** (DUA = durée d'utilité administrative) ;
 * - **Activités** (plan de classement) rattachées à une communicabilité ;
 * - **Types de lois**, **lois** et **articles de loi** (règle légale) ;
 * - **Rétentions** (DUL = durée d'utilité légale) rattachées à une activité
 *   (retention_activity) et à un article de loi (retention_law_articles), avec
 *   le sort final (E/T/C) ;
 * - **Notices** rattachées par défaut à une activité du plan.
 *
 * Idempotent : ne crée que ce qui manque, ne supprime rien.
 */
@Component
public class LifecycleSeeder {
  private static final Logger log = LoggerFactory.getLogger(LifecycleSeeder.class);

  private final JdbcTemplate jdbc;

  public LifecycleSeeder(JdbcTemplate jdbc) {
    this.jdbc = jdbc;
  }

  @Transactional
  public void run() {
    this.communicabilities();
    Long activityId = this.activities();
    this.laws();
    Long retentionId = this.retentions();
    this.attachRecords(activityId, retentionId);
    log.info("Cycle de vie DUA/DUL : communicabilités, activités, lois, rétentions et rattachements vérifiés.");
  }

  private void communicabilities() {
    for (Object[] row : List.of(
        new Object[] {"COM-5", "5 ans", 5, "Communicable après 5 ans"},
        new Object[] {"COM-10", "10 ans", 10, "Communicable après 10 ans"},
        new Object[] {"COM-30", "30 ans", 30, "Communicable après 30 ans"},
        new Object[] {"COM-0", "Immédiat", 0, "Communicable immédiatement"})) {
      String code = (String) row[0];
      if (this.commId(code) == null) {
        this.jdbc.update(
            "insert into communicabilities (code, name, duration, description, created_at, updated_at) values (?, ?, ?, ?, now(), now())",
            code, row[1], row[2], row[3]);
      }
    }
  }

  private Long commId(String code) {
    return this.jdbc.query("select id from communicabilities where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  /** Crée le plan de classement (activités propres) si vide, rattachées à une communicabilité. */
  private Long activities() {
    Long existing = this.jdbc.query("select id from activities where code = 'ACC-01'",
        rs -> rs.next() ? rs.getLong(1) : null);
    if (existing != null) return existing;

    Long comImm = this.commId("COM-0");
    Long com5 = this.commId("COM-5");
    Long com10 = this.commId("COM-10");
    Long com30 = this.commId("COM-30");

    // Direction générale + directions (orgs DG/DF/DRH/DADA existent via OrganisationSeeder).
    Long dgId = this.orgId("DG");
    Long dfId = this.orgId("DF");
    Long drhId = this.orgId("DRH");
    Long dadaId = this.orgId("DADA");

    Long adminId = this.activity("ACC-01", "Administration générale", comImm, dgId, null);
    Long financeId = this.activity("FIN-01", "Gestion budgétaire", com10, dfId, null);
    Long rhId = this.activity("RH-01", "Gestion des ressources humaines", com30, drhId, null);
    Long archId = this.activity("ARCH-01", "Gestion des archives", com5, dadaId, null);

    this.activity("ACC-0101", "Courrier général", comImm, dgId, adminId);
    this.activity("FIN-0101", "Préparation du budget", com10, dfId, financeId);
    this.activity("FIN-0102", "Exécution budgétaire", com10, dfId, financeId);
    this.activity("RH-0101", "Dossiers du personnel", com30, drhId, rhId);
    this.activity("RH-0102", "Formation", com30, drhId, rhId);
    this.activity("ARCH-0101", "Versements et bordereaux", com5, dadaId, archId);
    this.activity("ARCH-0102", "Déclassement et élimination", com5, dadaId, archId);

    // Règle par défaut sur les sous-activités principales (pour le rattachement des notices).
    if (comImm != null) {
      this.communicabilityOnActivity("ACC-0101", comImm);
    }
    return this.activityId("ACC-0101") != null ? this.activityId("ACC-0101") : adminId;
  }

  private void communicabilityOnActivity(String activityCode, Long commId) {
    Long actId = this.activityId(activityCode);
    if (actId != null) {
      this.jdbc.update("update activities set communicability_id = ? where id = ?", commId, actId);
    }
  }

  private Long activity(String code, String name, Long commId, Long orgId, Long parentId) {
    Long existing = this.jdbc.query("select id from activities where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
    if (existing != null) return existing;
    this.jdbc.update(
        "insert into activities (code, name, observation, parent_id, communicability_id, created_at, updated_at) values (?, ?, ?, ?, ?, now(), now())",
        code, name, name, parentId, commId);
    return this.jdbc.query("select id from activities where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  private Long orgId(String code) {
    return this.jdbc.query("select id from organisations where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  /** Types de lois, lois et articles. */
  private void laws() {
    Long lawTypeId = this.lawTypeId("LOI");
    if (lawTypeId == null) {
      this.jdbc.update(
          "insert into law_types (name, description, created_at, updated_at) values ('Loi', 'Textes législatifs', now(), now())");
      lawTypeId = this.lawTypeId("LOI");
    }
    Long loiArchivesId = this.lawId("L-ARCH");
    if (loiArchivesId == null) {
      this.jdbc.update(
          "insert into laws (code, name, description, publish_date, law_type_id, created_at, updated_at) values ('L-ARCH', 'Loi sur les archives', 'Régime juridique des archives publiques', '1979-01-03', ?, now(), now())",
          lawTypeId);
      loiArchivesId = this.lawId("L-ARCH");
    }
    this.article("A-ARCH-1", "Article 1", "Définition des archives publiques", loiArchivesId);
    this.article("A-ARCH-2", "Article 2", "Communication des archives publiques", loiArchivesId);
  }

  private Long lawTypeId(String name) {
    return this.jdbc.query("select id from law_types where name = ?",
        rs -> rs.next() ? rs.getLong(1) : null, name);
  }

  private Long lawId(String code) {
    return this.jdbc.query("select id from laws where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  private Long article(String code, String name, String description, Long lawId) {
    Long existing = this.jdbc.query("select id from law_articles where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
    if (existing != null) return existing;
    this.jdbc.update(
        "insert into law_articles (code, name, description, law_id, created_at, updated_at) values (?, ?, ?, ?, now(), now())",
        code, name, description, lawId);
    return this.jdbc.query("select id from law_articles where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  /** Rétentions (DUL) liées aux activités et aux articles de loi. */
  private Long retentions() {
    Long loiArchivesId = this.lawId("L-ARCH");
    Long article2Id = this.jdbc.query("select id from law_articles where code = 'A-ARCH-2'",
        rs -> rs.next() ? rs.getLong(1) : null);
    Long sortE = this.sortId("E");
    Long sortC = this.sortId("C");
    Long sortT = this.sortId("T");

    Long rElim = this.retention("R-ELEM", "Élimination après 10 ans", 10, sortE, "FIN-0102", article2Id);
    Long rCons = this.retention("R-CONS", "Conservation définitive", 30, sortC, "ARCH-0101", article2Id);
    Long rTri = this.retention("R-TRI", "Tri après 15 ans", 15, sortT, "RH-0102", article2Id);
    Long rConsAdmin = this.retention("R-ADM", "Conservation administrative 5 ans", 5, sortE, "ACC-0101", article2Id);

    // Règle par défaut sur l'activité "Administration générale" (serve aussi de fallback).
    if (rCons != null && this.activityId("ARCH-0102") != null) {
      this.linkActivity(rCons, this.activityId("ARCH-0102"));
    }
    return rConsAdmin != null ? rConsAdmin : rCons;
  }

  private Long retention(String code, String name, int duration, Long sortId, String activityCode, Long articleId) {
    Long existing = this.jdbc.query("select id from retentions where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
    Long retentionId;
    if (existing == null) {
      this.jdbc.update(
          "insert into retentions (code, name, duration, sort_id, created_at, updated_at) values (?, ?, ?, ?, now(), now())",
          code, name, duration, sortId);
      retentionId = this.jdbc.query("select id from retentions where code = ?",
          rs -> rs.next() ? rs.getLong(1) : null, code);
    } else {
      retentionId = existing;
    }
    Long actId = this.activityId(activityCode);
    if (actId != null) this.linkActivity(retentionId, actId);
    if (articleId != null) this.linkArticle(retentionId, articleId);
    return retentionId;
  }

  private void linkActivity(Long retentionId, Long activityId) {
    Integer exists = this.jdbc.query(
        "select count(*) from retention_activity where retention_id = ? and activity_id = ?",
        rs -> rs.next() ? rs.getInt(1) : 0, retentionId, activityId);
    if (exists == 0) {
      // Pivot pur (sans timestamps).
      this.jdbc.update(
          "insert into retention_activity (retention_id, activity_id) values (?, ?)",
          retentionId, activityId);
    }
  }

  private void linkArticle(Long retentionId, Long articleId) {
    Integer exists = this.jdbc.query(
        "select count(*) from retention_law_articles where retention_id = ? and law_article_id = ?",
        rs -> rs.next() ? rs.getInt(1) : 0, retentionId, articleId);
    if (exists == 0) {
      this.jdbc.update(
          "insert into retention_law_articles (retention_id, law_article_id, created_at, updated_at) values (?, ?, now(), now())",
          retentionId, articleId);
    }
  }

  private Long activityId(String code) {
    return this.jdbc.query("select id from activities where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  private Long sortId(String code) {
    return this.jdbc.query("select id from sorts where code = ?",
        rs -> rs.next() ? rs.getLong(1) : null, code);
  }

  /**
   * Rattache les notices au plan de classement :
   * - les notices sans activité vont sur l'activité par défaut ;
   * - les notices encore sur la racine (plan vide, pas de rétention) sont réparties
   *   sur les sous-activités qui portent une rétention, pour alimenter le cycle de vie.
   */
  private void attachRecords(Long defaultActivityId, Long defaultRetentionId) {
    if (defaultActivityId == null) return;
    int updated = this.jdbc.update(
        "update records set activity_id = ? where activity_id is null and deleted_at is null",
        defaultActivityId);
    if (updated > 0) {
      log.info("  {} notices rattachées au plan de classement (activité par défaut).", updated);
    }
    this.redistributeRecords(defaultActivityId);
  }

  /** Répartit les notices de la racine sur les sous-activités portant une rétention. */
  private void redistributeRecords(Long rootActivityId) {
    List<Long> targets =
        this.jdbc.query(
            "select distinct ra.activity_id from retention_activity ra order by ra.activity_id",
            (rs, rowNum) -> rs.getLong(1));
    if (targets.isEmpty()) return;
    List<Long> records =
        this.jdbc.query(
            "select id from records where activity_id = ? and deleted_at is null",
            (rs, rowNum) -> rs.getLong(1),
            rootActivityId);
    for (int i = 0; i < records.size(); i++) {
      Long target = targets.get(i % targets.size());
      this.jdbc.update("update records set activity_id = ? where id = ?", target, records.get(i));
    }
    if (!records.isEmpty()) {
      log.info(
          "  {} notices réparties sur les sous-activités du plan (cycle de vie alimenté).", records.size());
    }
  }
}
