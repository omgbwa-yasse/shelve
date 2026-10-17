package com.shelve.records.controller;

import com.shelve.common.Json;
import com.shelve.common.Paging;
import com.shelve.exception.ApiException;
import com.shelve.exception.ValidationException;
import com.shelve.records.service.RecordIncludes;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.security.DigestInputStream;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.HexFormat;
import java.util.List;
import java.util.Map;
import java.util.UUID;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.ResponseEntity;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.jdbc.support.GeneratedKeyHolder;
import org.springframework.jdbc.support.KeyHolder;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.multipart.MultipartFile;

/**
 * Sous-ressources d'une notice : pièces jointes (fichiers numériques, table
 * `attachments` reliée par `record_physical_attachment`) et auteurs
 * (`record_author`).
 */
@Transactional
@RestController
@RequestMapping(value = {"/api/v1/records/{id}"})
public class RecordLinksController {
  private final JdbcTemplate jdbc;
  private final RecordIncludes includes;
  private final Path storageRoot;

  public RecordLinksController(
      JdbcTemplate jdbc,
      RecordIncludes includes,
      @Value("${app.storage.records:storage/records}") String storageRoot) {
    this.jdbc = jdbc;
    this.includes = includes;
    this.storageRoot = Path.of(storageRoot).toAbsolutePath().normalize();
  }

  /* ------------------------------------------------------------------ */
  /*  Pièces jointes                                                     */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/attachments"})
  public Map<String, Object> attachments(@PathVariable Long id, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    this.assertInScope(id, auth);
    List<Map<String, Object>> rows = this.includes.attachmentsOf(id);
    return Paging.envelope(rows, rows.size(), 1, Math.max(rows.size(), 1), request);
  }

  /** POST /api/v1/records/{id}/attachments/upload — multipart `file`. */
  @PostMapping(value = {"/attachments/upload"})
  public ResponseEntity<Map<String, Object>> upload(
      @PathVariable Long id,
      @RequestParam("file") MultipartFile file,
      @RequestParam(value = "name", required = false) String name,
      @RequestParam(value = "description", required = false) String description)
      throws IOException {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    this.assertInScope(id, auth);
    if (file == null || file.isEmpty()) {
      throw ValidationException.single("file", "The file field is required.");
    }

    String original = name != null && !name.isBlank() ? name : file.getOriginalFilename();
    String safeName =
        original == null || original.isBlank()
            ? "fichier"
            : Path.of(original).getFileName().toString().replaceAll("[\\r\\n\"]", "_");
    String ext =
        safeName.contains(".")
            ? safeName.substring(safeName.lastIndexOf('.') + 1).toLowerCase()
            : "";
    if (ext.length() > 10) {
      ext = ext.substring(0, 10);
    }

    // Stockage : storage/records/{id}/{uuid}.{ext} — le nom d'origine reste en base.
    String relative = id + "/" + UUID.randomUUID() + (ext.isEmpty() ? "" : "." + ext);
    Path target = this.storageRoot.resolve(relative).normalize();
    Files.createDirectories(target.getParent());

    MessageDigest md5;
    MessageDigest sha512;
    try {
      md5 = MessageDigest.getInstance("MD5");
      sha512 = MessageDigest.getInstance("SHA-512");
    } catch (NoSuchAlgorithmException e) {
      throw new IllegalStateException(e);
    }
    try (InputStream in =
        new DigestInputStream(new DigestInputStream(file.getInputStream(), md5), sha512)) {
      Files.copy(in, target, StandardCopyOption.REPLACE_EXISTING);
    }
    String md5Hex = HexFormat.of().formatHex(md5.digest());
    String shaHex = HexFormat.of().formatHex(sha512.digest());

    Integer nextOrder =
        this.jdbc.queryForObject(
            "select coalesce(max(a.display_order), -1) + 1 from attachments a"
                + " join record_physical_attachment rpa on rpa.attachment_id = a.id where rpa.record_id = ?",
            Integer.class,
            id);
    String storedName = safeName.length() > 100 ? safeName.substring(0, 100) : safeName;
    String finalExt = ext;
    KeyHolder keys = new GeneratedKeyHolder();
    this.jdbc.update(
        con -> {
          var ps =
              con.prepareStatement(
                  "insert into attachments (path, name, description, crypt, size, crypt_sha512,"
                      + " file_hash_md5, type, is_primary, display_order, mime_type, file_extension,"
                      + " creator_id, created_at, updated_at)"
                      + " values (?, ?, ?, ?, ?, ?, ?, 'record', ?, ?, ?, ?, ?, now(), now())",
                  java.sql.Statement.RETURN_GENERATED_KEYS);
          ps.setString(1, relative);
          ps.setString(2, storedName);
          ps.setString(3, description);
          ps.setString(4, md5Hex);
          ps.setLong(5, file.getSize());
          ps.setString(6, shaHex);
          ps.setString(7, md5Hex);
          ps.setBoolean(8, nextOrder == null || nextOrder == 0);
          ps.setInt(9, nextOrder == null ? 0 : nextOrder);
          ps.setString(10, file.getContentType());
          ps.setString(11, finalExt.isEmpty() ? null : finalExt);
          ps.setLong(12, auth.user().getId());
          return ps;
        },
        keys);
    long attachmentId = keys.getKey().longValue();
    this.jdbc.update(
        "insert into record_physical_attachment (record_id, attachment_id, created_at, updated_at)"
            + " values (?, ?, now(), now())",
        id,
        attachmentId);

    Map<String, Object> link =
        this.includes.attachmentsOf(id).stream()
            .filter(l -> Long.valueOf(attachmentId).equals(((Number) l.get("attachment_id")).longValue()))
            .findFirst()
            .orElseThrow();
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {"/api/v1/records/" + id + "/attachments/" + attachmentId + "/download"}))
        .body(Json.of("data", link));
  }

  /** GET …/attachments/{attachmentId}/download[?download=1] — aperçu inline ou téléchargement. */
  @GetMapping(value = {"/attachments/{attachmentId}/download"})
  public void download(
      @PathVariable Long id,
      @PathVariable Long attachmentId,
      @RequestParam(value = "download", required = false) String download,
      HttpServletResponse response)
      throws IOException {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    this.assertInScope(id, auth);
    Map<String, Object> row = this.attachmentRow(id, attachmentId);
    Path path = this.storageRoot.resolve(String.valueOf(row.get("path"))).normalize();
    if (!path.startsWith(this.storageRoot) || !Files.isRegularFile(path)) {
      throw ApiException.notFound("Fichier introuvable sur le stockage.");
    }
    String mime = row.get("mime_type") != null ? String.valueOf(row.get("mime_type")) : "application/octet-stream";
    String filename = String.valueOf(row.get("name")).replace("\"", "_");
    boolean asAttachment = download != null && !download.equals("0") && !download.equals("false");
    response.setContentType(mime);
    response.setContentLengthLong(Files.size(path));
    response.setHeader("X-Content-Type-Options", "nosniff");
    response.setHeader(
        "Content-Disposition",
        (asAttachment ? "attachment" : "inline")
            + "; filename=\"" + filename + "\"; filename*=UTF-8''"
            + java.net.URLEncoder.encode(filename, java.nio.charset.StandardCharsets.UTF_8).replace("+", "%20"));
    Files.copy(path, response.getOutputStream());
  }

  @DeleteMapping(value = {"/attachments/{attachmentId}"})
  public ResponseEntity<Void> deleteAttachment(@PathVariable Long id, @PathVariable Long attachmentId)
      throws IOException {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    this.assertInScope(id, auth);
    Map<String, Object> row = this.attachmentRow(id, attachmentId);
    this.jdbc.update(
        "delete from record_physical_attachment where record_id = ? and attachment_id = ?", id, attachmentId);
    Long otherLinks =
        this.jdbc.queryForObject(
            "select count(*) from record_physical_attachment where attachment_id = ?", Long.class, attachmentId);
    if (otherLinks == null || otherLinks == 0) {
      this.jdbc.update("delete from attachments where id = ?", attachmentId);
      Path path = this.storageRoot.resolve(String.valueOf(row.get("path"))).normalize();
      if (path.startsWith(this.storageRoot)) {
        Files.deleteIfExists(path);
      }
    }
    return ResponseEntity.noContent().build();
  }

  private Map<String, Object> attachmentRow(Long recordId, Long attachmentId) {
    List<Map<String, Object>> rows =
        this.jdbc.queryForList(
            "select a.id, a.path, a.name, a.mime_type from attachments a"
                + " join record_physical_attachment rpa on rpa.attachment_id = a.id"
                + " where rpa.record_id = ? and a.id = ?",
            recordId,
            attachmentId);
    if (rows.isEmpty()) {
      throw ApiException.notFound();
    }
    return rows.get(0);
  }

  /* ------------------------------------------------------------------ */
  /*  Auteurs                                                            */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/authors"})
  public Map<String, Object> authors(@PathVariable Long id, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    this.assertInScope(id, auth);
    List<Map<String, Object>> rows =
        this.jdbc.queryForList(
            "select a.id, a.name, a.type_id, a.lifespan from record_author ra"
                + " join authors a on a.id = ra.author_id where ra.record_id = ? order by a.name",
            id);
    return Paging.envelope(rows, rows.size(), 1, Math.max(rows.size(), 1), request);
  }

  @PostMapping(value = {"/authors"})
  public ResponseEntity<Map<String, Object>> attachAuthor(
      @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    this.assertInScope(id, auth);
    Long authorId = body.get("author_id") == null ? null : parseLong(body.get("author_id"));
    if (authorId == null) {
      throw ValidationException.single("author_id", "The author id field is required.");
    }
    List<Map<String, Object>> author =
        this.jdbc.queryForList("select id, name, type_id, lifespan from authors where id = ?", authorId);
    if (author.isEmpty()) {
      throw ValidationException.single("author_id", "Cet auteur n'existe pas.");
    }
    Long already =
        this.jdbc.queryForObject(
            "select count(*) from record_author where record_id = ? and author_id = ?", Long.class, id, authorId);
    if (already == null || already == 0) {
      this.jdbc.update(
          "insert into record_author (record_id, author_id, created_at, updated_at) values (?, ?, now(), now())",
          id,
          authorId);
    }
    return ResponseEntity.status(201).body(Json.of("data", author.get(0)));
  }

  @DeleteMapping(value = {"/authors/{authorId}"})
  public ResponseEntity<Void> detachAuthor(@PathVariable Long id, @PathVariable Long authorId) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    this.assertInScope(id, auth);
    this.jdbc.update("delete from record_author where record_id = ? and author_id = ?", id, authorId);
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */

  private void assertInScope(Long recordId, AuthenticatedUser auth) {
    Long n =
        this.jdbc.queryForObject(
            "select count(*) from records where id = ? and organisation_id = ? and deleted_at is null",
            Long.class,
            recordId,
            auth.user().getCurrentOrganisationId());
    if (n == null || n == 0) {
      throw ApiException.notFound();
    }
  }

  private static Long parseLong(Object value) {
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
