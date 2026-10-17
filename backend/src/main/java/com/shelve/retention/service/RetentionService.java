package com.shelve.retention.service;

import com.shelve.records.entity.Record;
import com.shelve.records.repository.RecordRepository;
import com.shelve.referentials.entity.Activity;
import com.shelve.referentials.entity.Communicability;
import com.shelve.referentials.repository.ActivityRepository;
import com.shelve.referentials.repository.CommunicabilityRepository;
import com.shelve.referentials.entity.Law;
import com.shelve.referentials.entity.LawArticle;
import com.shelve.referentials.repository.LawArticleRepository;
import com.shelve.referentials.repository.LawRepository;
import com.shelve.referentials.entity.Sort;
import com.shelve.referentials.repository.SortRepository;
import com.shelve.retention.entity.Retention;
import com.shelve.retention.entity.RetentionActivity;
import com.shelve.retention.entity.RetentionLawArticle;
import com.shelve.retention.repository.RetentionActivityRepository;
import com.shelve.retention.repository.RetentionLawArticleRepository;
import com.shelve.retention.repository.RetentionRepository;
import java.time.LocalDate;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.stream.Collectors;
import org.springframework.stereotype.Service;

/**
 * Cycle de vie des notices — aligné Constellio.
 *
 * Trois stades selon la date de référence (endDate sinon dateExact) :
 *  - AVANT DUA  : durée d'utilité administrative (communicabilité) non écoulée ;
 *  - APRÈS DUA  : communicabilité écoulée, mais durée légale (rétention) pas encore ;
 *  - APRÈS DUL  : rétention (durée d'utilité légale) écoulée — le sort final (E/T/C)
 *    s'applique.
 *
 * La rétention est rattachée à l'activité (`retention_activity`) et à la loi
 * (`retention_law_articles` → law_article → law).
 */
@Service
public class RetentionService {
  private final RecordRepository recordRepository;
  private final ActivityRepository activityRepository;
  private final CommunicabilityRepository communicabilityRepository;
  private final RetentionRepository retentionRepository;
  private final RetentionActivityRepository retentionActivityRepository;
  private final RetentionLawArticleRepository retentionLawArticleRepository;
  private final LawArticleRepository lawArticleRepository;
  private final LawRepository lawRepository;
  private final SortRepository sortRepository;

  public RetentionService(
      RecordRepository recordRepository,
      ActivityRepository activityRepository,
      CommunicabilityRepository communicabilityRepository,
      RetentionRepository retentionRepository,
      RetentionActivityRepository retentionActivityRepository,
      RetentionLawArticleRepository retentionLawArticleRepository,
      LawArticleRepository lawArticleRepository,
      LawRepository lawRepository,
      SortRepository sortRepository) {
    this.recordRepository = recordRepository;
    this.activityRepository = activityRepository;
    this.communicabilityRepository = communicabilityRepository;
    this.retentionRepository = retentionRepository;
    this.retentionActivityRepository = retentionActivityRepository;
    this.retentionLawArticleRepository = retentionLawArticleRepository;
    this.lawArticleRepository = lawArticleRepository;
    this.lawRepository = lawRepository;
    this.sortRepository = sortRepository;
  }

  /** Cycle de vie complet de toutes les notices d'une organisation. */
  public List<Map<String, Object>> lifecycle(Long organisationId) {
    List<Record> records =
        this.recordRepository.findAll((root, q, cb) -> cb.equal(root.get("organisationId"), organisationId));
    return records.stream()
        .map(r -> this.stage(r))
        .sorted((a, b) -> String.valueOf(a.get("stage")).compareTo(String.valueOf(b.get("stage"))))
        .collect(Collectors.toList());
  }

  /** Notices d'un stade donné (avant_dua | apres_dua | apres_dul). */
  public List<Map<String, Object>> lifecycleStage(Long organisationId, String stage) {
    return this.lifecycle(organisationId).stream()
        .filter(m -> stage.equals(m.get("stage")))
        .collect(Collectors.toList());
  }

  /** Stade + infos cycle de vie d'une notice. */
  public Map<String, Object> stage(Record r) {
    LocalDate ref = r.getEndDate() != null ? r.getEndDate() : r.getDateExact();
    Map<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("activity_id", r.getActivityId());
    map.put("status_id", r.getStatusId());
    map.put("level_id", r.getLevelId());
    map.put("organisation_id", r.getOrganisationId());
    map.put("start_date", r.getStartDate() != null ? r.getStartDate().toString() : null);
    map.put("end_date", r.getEndDate() != null ? r.getEndDate().toString() : null);
    map.put("date_exact", r.getDateExact() != null ? r.getDateExact().toString() : null);
    map.put("reference_date", ref != null ? ref.toString() : null);

    // Communicabilité (DUA) de l'activité.
    Activity activity = r.getActivityId() != null
        ? this.activityRepository.findById(r.getActivityId()).orElse(null)
        : null;
    Communicability communicability = null;
    if (activity != null && activity.getCommunicabilityId() != null) {
      communicability = this.communicabilityRepository.findById(activity.getCommunicabilityId()).orElse(null);
    }
    int duaYears = communicability != null && communicability.getDuration() != null
        ? communicability.getDuration()
        : 0;

    // Rétention (DUL) + sort + loi de l'activité.
    Retention retention = this.retentionForActivity(r.getActivityId());
    Sort sort = retention != null
        ? this.sortRepository.findById(retention.getSortId()).orElse(null)
        : null;
    Map<String, Object> lawInfo = this.lawForRetention(retention);
    int dulYears = retention != null && retention.getDuration() != null ? retention.getDuration() : 0;

    map.put("dua_years", duaYears);
    map.put("dul_years", dulYears);
    map.put("sort", sort != null ? sort.getCode() : null);
    map.put("sort_name", sort != null ? sort.getName() : null);
    map.put("retention", this.brief(retention, "retention"));
    map.put("law", lawInfo);

    // Calcul des dates prévues.
    LocalDate duaEnd = this.addYears(ref, duaYears);
    LocalDate dulEnd = this.addYears(ref, dulYears);
    map.put("dua_end_date", duaEnd != null ? duaEnd.toString() : null);
    map.put("dul_end_date", dulEnd != null ? dulEnd.toString() : null);

    // Stade.
    String stage = this.computeStage(ref, duaYears, dulYears);
    map.put("stage", stage);
    return map;
  }

  /** Stade : avant_dua | apres_dua | apres_dul. */
  public String computeStage(LocalDate ref, int duaYears, int dulYears) {
    if (ref == null) return "apres_dul";
    LocalDate today = LocalDate.now();
    long elapsedDays = ChronoUnit.DAYS.between(ref, today);
    if (dulYears > 0 && elapsedDays > (long) dulYears * 365L) return "apres_dul";
    if (duaYears > 0 && elapsedDays > (long) duaYears * 365L) return "apres_dua";
    return "avant_dua";
  }

  /** Rétention rattachée à l'activité (via retention_activity). */
  public Retention retentionForActivity(Long activityId) {
    if (activityId == null) return null;
    List<RetentionActivity> links =
        this.retentionActivityRepository.findAll(
            (root, q, cb) -> cb.equal(root.get("id").get("activityId"), activityId));
    for (RetentionActivity link : links) {
      Optional<Retention> r = this.retentionRepository.findById(link.getRetentionId());
      if (r.isPresent()) return r.get();
    }
    return null;
  }

  /** Loi rattachée à la rétention (via retention_law_articles → law_article → law). */
  public Map<String, Object> lawForRetention(Retention retention) {
    if (retention == null) return null;
    List<RetentionLawArticle> links =
        this.retentionLawArticleRepository.findAll(
            (root, q, cb) -> cb.equal(root.get("id").get("retentionId"), retention.getId()));
    for (RetentionLawArticle link : links) {
      Optional<LawArticle> article = this.lawArticleRepository.findById(link.getLawArticleId());
      if (article.isPresent() && article.get().getLawId() != null) {
        Optional<Law> law = this.lawRepository.findById(article.get().getLawId());
        if (law.isPresent()) {
          Map<String, Object> m = new LinkedHashMap<String, Object>();
          m.put("law_article_id", article.get().getId());
          m.put("law_article_code", article.get().getCode());
          m.put("law_article_name", article.get().getName());
          m.put("law_id", law.get().getId());
          m.put("law_code", law.get().getCode());
          m.put("law_name", law.get().getName());
          m.put("law_type_id", law.get().getLawTypeId());
          return m;
        }
      }
    }
    return null;
  }

  private Map<String, Object> brief(Retention r, String prefix) {
    if (r == null) return null;
    Map<String, Object> m = new LinkedHashMap<String, Object>();
    m.put("id", r.getId());
    m.put("code", r.getCode());
    m.put("name", r.getName());
    m.put("duration", r.getDuration());
    m.put("sort_id", r.getSortId());
    return m;
  }

  private LocalDate addYears(LocalDate date, int years) {
    if (date == null) return null;
    return date.plusYears(years);
  }
}
