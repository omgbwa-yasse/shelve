package com.shelve.records.controller;

import com.shelve.common.Json;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.exception.ApiException;
import com.shelve.exception.ValidationException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.servlet.http.HttpServletRequest;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/** Référentiel des auteurs (producteurs) : `authors`, `author_types`, `author_contacts`. */
@Transactional
@RestController
@RequestMapping(value = {"/api/v1"})
public class AuthorController {
  private static final String COLUMNS =
      "a.id, a.type_id, t.name as type_name, a.name, a.parallel_name, a.other_name, a.lifespan,"
          + " a.locations, a.parent_id, p.name as parent_name, a.created_at, a.updated_at";
  private static final String FROM =
      " from authors a left join author_types t on t.id = a.type_id left join authors p on p.id = a.parent_id";
  private static final List<String> TEXT_FIELDS =
      List.of("name", "parallel_name", "other_name", "lifespan", "locations");
  private static final List<String> CONTACT_FIELDS =
      List.of("phone1", "phone2", "email", "address", "website", "fax", "other", "po_box");

  private final JdbcTemplate jdbc;

  public AuthorController(JdbcTemplate jdbc) {
    this.jdbc = jdbc;
  }

  /* ------------------------------------------------------------------ */
  /*  Auteurs                                                            */
  /* ------------------------------------------------------------------ */

  /** GET /api/v1/authors?filter[name]=…&page=…&page.size=… */
  @GetMapping(value = {"/authors"})
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_viewAny");
    QueryParams qp = QueryParams.parse(request);
    String q = request.getParameter("filter[name]");
    if (q == null) {
      q = request.getParameter("q");
    }
    String where = q == null || q.isBlank() ? "" : " where a.name like ?";
    List<Object> args = new ArrayList<>();
    if (!where.isEmpty()) {
      args.add("%" + q.trim() + "%");
    }
    Long total = this.jdbc.queryForObject("select count(*)" + FROM + where, Long.class, args.toArray());
    int page = (int) qp.getPageNumber();
    int size = (int) qp.getPageSize();
    args.add(size);
    args.add((page - 1) * size);
    List<Map<String, Object>> rows =
        this.jdbc.queryForList(
            "select " + COLUMNS + FROM + where + " order by a.name limit ? offset ?", args.toArray());
    return Paging.envelope(rows.stream().map(AuthorController::view).toList(), total == null ? 0 : total, page, size, request);
  }

  @GetMapping(value = {"/authors/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_view");
    Map<String, Object> author = this.find(id);
    author.put(
        "contacts", this.jdbc.queryForList("select * from author_contacts where author_id = ?", id));
    author.put(
        "records_count",
        this.jdbc.queryForObject("select count(*) from record_author where author_id = ?", Long.class, id));
    return Json.of("data", author);
  }

  @PostMapping(value = {"/authors"})
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_create");
    Map<String, Object> values = this.validate(body, null);
    if (values.get("type_id") == null) {
      values.put("type_id", this.defaultTypeId());
    }
    List<String> cols = new ArrayList<>(values.keySet());
    String sql =
        "insert into authors (" + String.join(", ", cols) + ", created_at, updated_at) values ("
            + "?, ".repeat(cols.size()) + "now(), now())";
    this.jdbc.update(sql, values.values().toArray());
    Long id = this.jdbc.queryForObject("select id from authors where name = ?", Long.class, values.get("name"));
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201).header("Location", new String[] {"/api/v1/authors/" + id}))
        .body(Json.of("data", this.find(id)));
  }

  @PatchMapping(value = {"/authors/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_update");
    this.find(id);
    Map<String, Object> values = this.validate(body, id);
    if (!values.isEmpty()) {
      List<Object> args = new ArrayList<>(values.values());
      args.add(id);
      this.jdbc.update(
          "update authors set " + String.join(" = ?, ", values.keySet()) + " = ?, updated_at = now() where id = ?",
          args.toArray());
    }
    return Json.of("data", this.find(id));
  }

  @DeleteMapping(value = {"/authors/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_delete");
    this.find(id);
    Long linked = this.jdbc.queryForObject("select count(*) from record_author where author_id = ?", Long.class, id);
    if (linked != null && linked > 0) {
      throw ApiException.conflict("Cet auteur est rattaché à " + linked + " notice(s) : détachez-le d'abord.");
    }
    Long children = this.jdbc.queryForObject("select count(*) from authors where parent_id = ?", Long.class, id);
    if (children != null && children > 0) {
      throw ApiException.conflict("Cet auteur a des auteurs rattachés : modifiez-les d'abord.");
    }
    this.jdbc.update("delete from author_contacts where author_id = ?", id);
    this.jdbc.update("delete from authors where id = ?", id);
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Types d'auteurs                                                    */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/author-types"})
  public Map<String, Object> types() {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_viewAny");
    this.defaultTypeId();
    return Json.of("data", this.jdbc.queryForList("select id, name, description from author_types order by name"));
  }

  /* ------------------------------------------------------------------ */
  /*  Contacts                                                           */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/author-contacts"})
  public Map<String, Object> contacts(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_contact_viewAny");
    String authorId = request.getParameter("filter[author_id]");
    List<Map<String, Object>> rows =
        authorId == null
            ? this.jdbc.queryForList("select * from author_contacts order by id")
            : this.jdbc.queryForList("select * from author_contacts where author_id = ? order by id", authorId);
    return Paging.envelope(rows, rows.size(), 1, Math.max(rows.size(), 1), request);
  }

  @PostMapping(value = {"/author-contacts"})
  public ResponseEntity<Map<String, Object>> storeContact(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_contact_create");
    Object authorId = body.get("author_id");
    if (authorId == null) {
      throw ValidationException.single("author_id", "The author id field is required.");
    }
    this.find(Long.valueOf(String.valueOf(authorId)));
    List<String> cols = new ArrayList<>(List.of("author_id"));
    List<Object> args = new ArrayList<>(List.of(authorId));
    for (String f : CONTACT_FIELDS) {
      if (body.containsKey(f)) {
        cols.add(f);
        args.add(body.get(f) == null ? null : String.valueOf(body.get(f)));
      }
    }
    this.jdbc.update(
        "insert into author_contacts (" + String.join(", ", cols) + ", created_at, updated_at) values ("
            + "?, ".repeat(cols.size()) + "now(), now())",
        args.toArray());
    Long id = this.jdbc.queryForObject("select max(id) from author_contacts", Long.class);
    return ResponseEntity.status(201)
        .body(Json.of("data", this.jdbc.queryForMap("select * from author_contacts where id = ?", id)));
  }

  @DeleteMapping(value = {"/author-contacts/{id}"})
  public ResponseEntity<Void> destroyContact(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "author_contact_delete");
    if (this.jdbc.update("delete from author_contacts where id = ?", id) == 0) {
      throw ApiException.notFound();
    }
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */

  private Map<String, Object> validate(Map<String, Object> body, Long id) {
    Map<String, Object> values = new LinkedHashMap<>();
    Map<String, List<String>> errors = new LinkedHashMap<>();
    for (String f : TEXT_FIELDS) {
      if (body.containsKey(f)) {
        String v = body.get(f) == null ? null : String.valueOf(body.get(f)).trim();
        if (v != null && v.length() > 100) {
          errors.put(f, List.of("Ce champ ne doit pas dépasser 100 caractères."));
        }
        values.put(f, v == null || v.isEmpty() ? null : v);
      }
    }
    if (id == null || body.containsKey("name")) {
      Object name = values.get("name");
      if (name == null) {
        errors.put("name", List.of("The name field is required."));
      } else {
        Long dup =
            this.jdbc.queryForObject(
                "select count(*) from authors where name = ? and id <> ?", Long.class, name, id == null ? 0 : id);
        if (dup != null && dup > 0) {
          errors.put("name", List.of("Un auteur porte déjà ce nom."));
        }
      }
    }
    if (body.containsKey("type_id")) {
      Long typeId = toLong(body.get("type_id"));
      if (typeId != null && this.count("author_types", typeId) == 0) {
        errors.put("type_id", List.of("Ce type d'auteur n'existe pas."));
      }
      values.put("type_id", typeId);
    }
    if (body.containsKey("parent_id")) {
      Long parentId = toLong(body.get("parent_id"));
      if (parentId != null) {
        if (parentId.equals(id)) {
          errors.put("parent_id", List.of("Un auteur ne peut pas être son propre parent."));
        } else if (this.count("authors", parentId) == 0) {
          errors.put("parent_id", List.of("L'auteur parent n'existe pas."));
        } else if (id != null && this.isDescendant(parentId, id)) {
          errors.put("parent_id", List.of("L'auteur parent ne peut pas être l'un de ses descendants."));
        }
      }
      values.put("parent_id", parentId);
    }
    if (!errors.isEmpty()) {
      throw ValidationException.of(errors);
    }
    return values;
  }

  private boolean isDescendant(Long candidate, Long ancestor) {
    Long cursor = candidate;
    for (int depth = 0; cursor != null && depth < 100; depth++) {
      if (cursor.equals(ancestor)) {
        return true;
      }
      List<Long> parents = this.jdbc.queryForList("select parent_id from authors where id = ?", Long.class, cursor);
      cursor = parents.isEmpty() ? null : parents.get(0);
    }
    return false;
  }

  /** `authors.type_id` est obligatoire : garantit au moins un type « Personne ». */
  private Long defaultTypeId() {
    List<Long> ids = this.jdbc.queryForList("select id from author_types order by id limit 1", Long.class);
    if (!ids.isEmpty()) {
      return ids.get(0);
    }
    this.jdbc.update(
        "insert into author_types (name, description, created_at, updated_at) values"
            + " ('Personne', 'Personne physique', now(), now()),"
            + " ('Collectivité', 'Organisation, service ou institution', now(), now()),"
            + " ('Famille', 'Famille ou groupe familial', now(), now())");
    return this.jdbc.queryForObject("select min(id) from author_types", Long.class);
  }

  private Map<String, Object> find(Long id) {
    List<Map<String, Object>> rows = this.jdbc.queryForList("select " + COLUMNS + FROM + " where a.id = ?", id);
    if (rows.isEmpty()) {
      throw ApiException.notFound();
    }
    return view(rows.get(0));
  }

  private static Map<String, Object> view(Map<String, Object> row) {
    Map<String, Object> v = new LinkedHashMap<>(row);
    Object typeName = v.remove("type_name");
    Object parentName = v.remove("parent_name");
    v.put("type", v.get("type_id") == null ? null : Map.of("id", v.get("type_id"), "name", String.valueOf(typeName)));
    v.put("parent", v.get("parent_id") == null ? null : Map.of("id", v.get("parent_id"), "name", String.valueOf(parentName)));
    return v;
  }

  private long count(String table, Long id) {
    Long n = this.jdbc.queryForObject("select count(*) from " + table + " where id = ?", Long.class, id);
    return n == null ? 0 : n;
  }

  private static Long toLong(Object value) {
    if (value == null || String.valueOf(value).isBlank()) {
      return null;
    }
    if (value instanceof Number n) {
      return n.longValue();
    }
    try {
      return Long.parseLong(String.valueOf(value).trim());
    } catch (NumberFormatException e) {
      return null;
    }
  }
}
