package com.shelve.records.service;

import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.stream.Collectors;
import org.springframework.jdbc.core.namedparam.MapSqlParameterSource;
import org.springframework.jdbc.core.namedparam.NamedParameterJdbcTemplate;
import org.springframework.stereotype.Service;

/**
 * Résout les relations demandées via `?include=` sur les notices, en lots (une
 * requête par relation pour toute la page) afin d'éviter le N+1.
 */
@Service
public class RecordIncludes {
  /** Relations « une à une » : include → (colonne FK, table, colonnes exposées). */
  private static final Map<String, String[]> BELONGS_TO = new LinkedHashMap<>();

  static {
    BELONGS_TO.put("type", new String[] {"type_id", "record_types", "id,code,name"});
    BELONGS_TO.put("level", new String[] {"level_id", "record_levels", "id,name"});
    BELONGS_TO.put("status", new String[] {"status_id", "record_statuses", "id,name"});
    BELONGS_TO.put("activity", new String[] {"activity_id", "activities", "id,code,name"});
    BELONGS_TO.put("parent", new String[] {"parent_id", "records", "id,code,name"});
    BELONGS_TO.put("organisation", new String[] {"organisation_id", "organisations", "id,code,name"});
    BELONGS_TO.put("creator", new String[] {"creator_id", "users", "id,name,surname,email"});
    BELONGS_TO.put("assignedUser", new String[] {"assigned_to", "users", "id,name,surname,email"});
    BELONGS_TO.put("approver", new String[] {"approved_by", "users", "id,name,surname,email"});
    BELONGS_TO.put(
        "confidentiality",
        new String[] {"confidentiality_id", "record_confidentialities", "id,code,name"});
    BELONGS_TO.put("accessLimit", new String[] {"access_limit_id", "communicabilities", "id,code,name"});
  }

  public static final List<String> INCLUDABLE =
      List.of(
          "type", "level", "status", "activity", "parent", "organisation", "creator",
          "assignedUser", "approver", "confidentiality", "accessLimit", "children", "authors",
          "keywords", "attachments", "mediums", "communications");

  private final NamedParameterJdbcTemplate jdbc;

  public RecordIncludes(NamedParameterJdbcTemplate jdbc) {
    this.jdbc = jdbc;
  }

  /** Enrichit en place les vues de notices (clés snake_case du mapper). */
  public void apply(List<Map<String, Object>> rows, Collection<String> includes) {
    if (rows.isEmpty() || includes.isEmpty()) {
      return;
    }
    Set<Long> ids = this.ids(rows, "id");
    for (String inc : includes) {
      String[] rel = BELONGS_TO.get(inc);
      if (rel != null) {
        this.belongsTo(rows, inc, rel[0], rel[1], rel[2]);
        continue;
      }
      switch (inc) {
        case "children" -> this.hasMany(rows, inc, ids,
            "select parent_id as owner_id, id, code, name from records"
                + " where parent_id in (:ids) and deleted_at is null order by code");
        case "authors" -> this.hasMany(rows, inc, ids,
            "select ra.record_id as owner_id, a.id, a.name from record_author ra"
                + " join authors a on a.id = ra.author_id where ra.record_id in (:ids) order by a.name");
        case "keywords" -> this.hasMany(rows, inc, ids,
            "select rk.record_id as owner_id, k.id, k.name from record_keyword rk"
                + " join keywords k on k.id = rk.keyword_id where rk.record_id in (:ids) order by k.name");
        case "attachments" -> this.attachments(rows, ids);
        // Pas encore portés côté Spring : listes vides plutôt qu'une erreur.
        case "mediums", "communications" -> rows.forEach(r -> r.put(inc, List.of()));
        default -> { }
      }
    }
  }

  /** Pièces jointes d'une notice, au format `{record_id, attachment_id, attachment: {...}}`. */
  public List<Map<String, Object>> attachmentsOf(Long recordId) {
    return this.attachmentRows(Set.of(recordId)).getOrDefault(recordId, List.of());
  }

  private void attachments(List<Map<String, Object>> rows, Set<Long> ids) {
    Map<Long, List<Map<String, Object>>> byRecord = this.attachmentRows(ids);
    for (Map<String, Object> r : rows) {
      r.put("attachments", byRecord.getOrDefault(toLong(r.get("id")), List.of()));
    }
  }

  private Map<Long, List<Map<String, Object>>> attachmentRows(Set<Long> ids) {
    List<Map<String, Object>> list =
        this.jdbc.queryForList(
            "select rpa.record_id, a.id, a.name, a.description, a.size, a.mime_type,"
                + " a.file_extension, a.is_primary, a.display_order, a.created_at"
                + " from record_physical_attachment rpa join attachments a on a.id = rpa.attachment_id"
                + " where rpa.record_id in (:ids) order by a.display_order, a.id",
            new MapSqlParameterSource("ids", ids));
    Map<Long, List<Map<String, Object>>> out = new HashMap<>();
    for (Map<String, Object> row : list) {
      Long recordId = toLong(row.remove("record_id"));
      Map<String, Object> attachment = new LinkedHashMap<>(row);
      Map<String, Object> link = new LinkedHashMap<>();
      link.put("record_id", recordId);
      link.put("attachment_id", attachment.get("id"));
      link.put("attachment", attachment);
      out.computeIfAbsent(recordId, k -> new ArrayList<>()).add(link);
    }
    return out;
  }

  private void belongsTo(
      List<Map<String, Object>> rows, String key, String fk, String table, String columns) {
    Set<Long> fks = this.ids(rows, fk);
    Map<Long, Map<String, Object>> byId = new HashMap<>();
    if (!fks.isEmpty()) {
      for (Map<String, Object> row :
          this.jdbc.queryForList(
              "select " + columns + " from " + table + " where id in (:ids)",
              new MapSqlParameterSource("ids", fks))) {
        byId.put(toLong(row.get("id")), row);
      }
    }
    for (Map<String, Object> r : rows) {
      r.put(key, byId.get(toLong(r.get(fk))));
    }
  }

  private void hasMany(List<Map<String, Object>> rows, String key, Set<Long> ids, String sql) {
    Map<Long, List<Map<String, Object>>> byOwner =
        this.jdbc.queryForList(sql, new MapSqlParameterSource("ids", ids)).stream()
            .collect(
                Collectors.groupingBy(
                    row -> toLong(row.remove("owner_id")), Collectors.toList()));
    for (Map<String, Object> r : rows) {
      r.put(key, byOwner.getOrDefault(toLong(r.get("id")), List.of()));
    }
  }

  private Set<Long> ids(List<Map<String, Object>> rows, String key) {
    Set<Long> out = new LinkedHashSet<>();
    for (Map<String, Object> r : rows) {
      Long id = toLong(r.get(key));
      if (id != null) {
        out.add(id);
      }
    }
    return out;
  }

  private static Long toLong(Object v) {
    return v instanceof Number n ? n.longValue() : null;
  }
}
