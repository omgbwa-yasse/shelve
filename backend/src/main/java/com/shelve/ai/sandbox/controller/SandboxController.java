package com.shelve.ai.sandbox.controller;

import com.shelve.ai.sandbox.dto.SandboxFileView;
import com.shelve.ai.sandbox.dto.SandboxView;
import com.shelve.ai.sandbox.entity.AiSandbox;
import com.shelve.ai.sandbox.entity.AiSandboxFile;
import com.shelve.ai.sandbox.repository.AiSandboxFileRepository;
import com.shelve.ai.sandbox.repository.AiSandboxRepository;
import com.shelve.ai.sandbox.service.SandboxService;
import com.shelve.common.Json;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.exception.ApiException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Sandbox Python de l'assistant IA (D14) — parité avec Laravel.
 *
 * <p>Sécurité : chaque action exige la permission `ai_sandbox_*` (scopée à l'org
 * courante) et le sandbox doit appartenir à l'agent connecté DANS son organisation
 * courante (R03) — un changement d'org en cours de session bloque l'accès résiduel.
 */
@RestController
@RequestMapping(value = {"/api/v1/ai/sandboxes"})
public class SandboxController {
  private static final List<String> SORTABLE =
      List.of("id", "organisation_id", "user_id", "conversation_id", "name", "status", "created_at", "updated_at");
  private static final List<String> FILTERABLE = SORTABLE;

  private final SandboxService sandboxService;
  private final AiSandboxRepository sandboxRepository;
  private final AiSandboxFileRepository fileRepository;
  private final com.shelve.ai.sandbox.service.SandboxCapabilities capabilities;

  public SandboxController(
      SandboxService sandboxService,
      AiSandboxRepository sandboxRepository,
      AiSandboxFileRepository fileRepository,
      com.shelve.ai.sandbox.service.SandboxCapabilities capabilities) {
    this.sandboxService = sandboxService;
    this.sandboxRepository = sandboxRepository;
    this.fileRepository = fileRepository;
    this.capabilities = capabilities;
  }

  @GetMapping(value = {"/capabilities"})
  public Map<String, Object> capabilities() {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_skill_view");

    return Json.of("data", this.capabilities.manifest());
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_viewAny");

    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, List.of());

    return Paging.page(
        this.sandboxRepository, (root, q, cb) -> cb.equal(root.get("userId"), auth.user().getId()),
        qp, SORTABLE, "createdAt", request, this::mapper);
  }

  @PostMapping
  public Map<String, Object> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_open");

    AiSandbox sb = this.sandboxService.open(auth.user().getId(), auth.user().getCurrentOrganisationId(), body);
    return Json.of("data", this.mapper(sb));
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_view");

    AiSandbox sb = this.findOwned(id, auth);
    return Json.of("data", this.mapper(sb));
  }

  @PostMapping(value = {"/{id}/files"})
  public Map<String, Object> write(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_write");

    AiSandbox sb = this.findOwned(id, auth);
    String section = str(body.get("section"), "core");
    String path = str(body.get("path"), "");
    String content = str(body.get("content"), "");

    AiSandboxFile file = this.sandboxService.write(sb, section, path, content);
    return Json.of("data", this.fileMapper(file));
  }

  @PostMapping(value = {"/{id}/run"})
  public Map<String, Object> run(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_run");

    AiSandbox sb = this.findOwned(id, auth);
    String script = str(body.get("script"), "core/main.py");

    Map<String, Object> result = new LinkedHashMap<>(this.sandboxService.run(sb, script));
    result.put("status", this.sandboxRepository.findById(sb.getId()).map(AiSandbox::getStatus).orElse(null));
    result.put("sandbox_id", sb.getId());

    return Json.of("data", result);
  }

  @PostMapping(value = {"/{id}/close"})
  public Map<String, Object> close(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_close");

    AiSandbox sb = this.findOwned(id, auth);
    List<AiSandboxFile> files = this.sandboxService.close(sb);

    return Json.of("data", Map.of("closed", true, "files", files.stream().map(this::fileMapper).toList()));
  }

  @GetMapping(value = {"/{id}/files"})
  public Map<String, Object> files(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_view");

    AiSandbox sb = this.findOwned(id, auth);
    return Json.of("data", this.fileRepository.findBySandboxId(sb.getId()).stream().map(this::fileMapper).toList());
  }

  @GetMapping(value = {"/{id}/files/{fileId}/download"})
  public void download(
      @PathVariable Long id, @PathVariable Long fileId, HttpServletResponse response) throws IOException {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "ai_sandbox_view");

    AiSandbox sb = this.findOwned(id, auth);
    AiSandboxFile file = this.fileRepository.findById(fileId)
        .filter(f -> f.getSandboxId().equals(sb.getId()))
        .orElseThrow(ApiException::notFound);

    Path path = Path.of(file.getPath());
    if (!Files.isRegularFile(path)) {
      throw ApiException.notFound();
    }

    response.setContentType(file.getMime() != null ? file.getMime() : "application/octet-stream");
    response.setHeader("Content-Disposition", "attachment; filename=\"" + file.getName() + "\"");
    Files.copy(path, response.getOutputStream());
  }

  // ----------------------------------------------------------------------
  //  Helpers
  // ----------------------------------------------------------------------

  private AiSandbox findOwned(Long id, AuthenticatedUser auth) {
    AiSandbox sb = this.sandboxRepository.findByIdAndUserId(id, auth.user().getId())
        .orElseThrow(ApiException::notFound);

    if (!sb.getOrganisationId().equals(auth.user().getCurrentOrganisationId())) {
      throw new ApiException(HttpStatus.FORBIDDEN, "Sandbox hors de votre organisation.");
    }
    return sb;
  }

  private Map<String, Object> mapper(AiSandbox sb) {
    LinkedHashMap<String, Object> m = new LinkedHashMap<>();
    m.put("id", sb.getId());
    m.put("organisation_id", sb.getOrganisationId());
    m.put("user_id", sb.getUserId());
    m.put("conversation_id", sb.getConversationId());
    m.put("name", sb.getName());
    m.put("pattern", sb.getPattern());
    m.put("engine", sb.getEngine());
    m.put("status", sb.getStatus());
    m.put("folder", sb.getFolder());
    m.put("last_output", sb.getLastOutput());
    m.put("expires_at", sb.getExpiresAt() != null ? sb.getExpiresAt().toString() : null);
    m.put("created_at", Json.timestamp(sb.getCreatedAt()));
    m.put("updated_at", Json.timestamp(sb.getUpdatedAt()));
    return m;
  }

  private Map<String, Object> fileMapper(AiSandboxFile f) {
    LinkedHashMap<String, Object> m = new LinkedHashMap<>();
    m.put("id", f.getId());
    m.put("sandbox_id", f.getSandboxId());
    m.put("section", f.getSection());
    m.put("path", f.getPath());
    m.put("name", f.getName());
    m.put("size", f.getSize());
    m.put("mime", f.getMime());
    m.put("hash", f.getHash());
    m.put("created_at", Json.timestamp(f.getCreatedAt()));
    return m;
  }

  private static String str(Object v, String dflt) {
    return v == null ? dflt : String.valueOf(v);
  }
}
