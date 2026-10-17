package com.shelve.ai.controller;

import com.shelve.ai.entity.AiConversation;
import com.shelve.ai.entity.AiMessage;
import com.shelve.ai.repository.AiConversationRepository;
import com.shelve.ai.repository.AiMessageRepository;
import com.shelve.ai.service.AiChatService;
import com.shelve.common.Json;
import com.shelve.exception.ApiException;
import com.shelve.organisation.entity.User;
import com.shelve.organisation.repository.UserRepository;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import jakarta.servlet.http.HttpServletRequest;
import java.time.Instant;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.function.Function;
import java.util.stream.Collectors;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Assistant IA (panneau latéral, onglets Chat/Historique + mention @IA).
 * Ressource strictement personnelle : isolation par user_id + organisation
 * (404 hors périmètre, jamais 403). `store` prend le premier message et
 * génère la réponse ; `sendMessage` poursuit le fil.
 */
@RestController
@RequestMapping(value = {"/api/v1/ai/conversations"})
public class AiConversationController {
  private final AiConversationRepository repo;
  private final AiMessageRepository messageRepo;
  private final UserRepository userRepo;
  private final AiChatService chat;

  public AiConversationController(
      AiConversationRepository repo,
      AiMessageRepository messageRepo,
      UserRepository userRepo,
      AiChatService chat) {
    this.repo = repo;
    this.messageRepo = messageRepo;
    this.userRepo = userRepo;
    this.chat = chat;
  }

  /** GET /api/v1/ai/conversations — historique (non archivé) de l'agent. */
  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Specification<AiConversation> spec =
        (root, q, cb) ->
            cb.and(
                cb.equal(root.get("userId"), auth.user().getId()),
                cb.isNull(root.get("archivedAt")));
    List<AiConversation> list =
        this.repo.findAll(spec).stream()
            .sorted((a, b) -> {
              Instant ta = a.getUpdatedAt() != null ? a.getUpdatedAt() : a.getCreatedAt();
              Instant tb = b.getUpdatedAt() != null ? b.getUpdatedAt() : b.getCreatedAt();
              return tb.compareTo(ta);
            })
            .toList();
    return Json.of(
        "data",
        list.stream()
            .map(
                c -> {
                  Map<String, Object> m = new LinkedHashMap<String, Object>();
                  m.put("id", c.getId());
                  m.put("title", c.getTitle());
                  m.put("mode", c.getMode());
                  m.put("messages_count", this.messageRepo.findByConversationIdOrderByCreatedAtAsc(c.getId()).size());
                  m.put("created_at", Json.timestamp(c.getCreatedAt()));
                  m.put("updated_at", Json.timestamp(c.getUpdatedAt()));
                  return m;
                })
            .toList());
  }

  /** GET /api/v1/ai/conversations/{id} — conversation avec ses messages. */
  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    AiConversation conv = this.findOwned(id, auth);
    Map<String, Object> map = this.baseMapper(conv);
    map.put(
        "messages",
        this.messageRepo.findByConversationIdOrderByCreatedAtAsc(conv.getId()).stream()
            .map(this::messageMapper)
            .toList());
    return Json.of("data", map);
  }

  /** POST /api/v1/ai/conversations — démarre une conversation avec un premier message. */
  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    String message = str(body.get("message"));
    if (message == null || message.isBlank()) {
      throw com.shelve.exception.ValidationException.single("message", "The message field is required.");
    }
    String pageContext = str(body.get("context"));
    String mode = str(body.get("mode"));
    if (mode == null || mode.isBlank()) mode = "manuel";

    AiConversation conv = new AiConversation();
    conv.setOrganisationId(auth.user().getCurrentOrganisationId());
    conv.setUserId(auth.user().getId());
    conv.setTitle(AiConversationController.titleFromMessage(message));
    conv.setContext(AiConversationController.toJson(pageContext));
    conv.setMode(mode);
    this.repo.save(conv);

    AiMessage userMsg = new AiMessage();
    userMsg.setConversationId(conv.getId());
    userMsg.setRole("user");
    userMsg.setContent(message);
    userMsg.setContext(AiConversationController.toJson(pageContext));
    this.messageRepo.save(userMsg);

    Map<String, Object> result =
        this.chat.reply(message, List.of(), pageContext, auth, mode);
    AiMessage assistantMsg = new AiMessage();
    assistantMsg.setConversationId(conv.getId());
    assistantMsg.setRole("assistant");
    assistantMsg.setContent((String) result.get("reply"));
    assistantMsg.setContext(this.serializeContext(result.get("context")));
    this.messageRepo.save(assistantMsg);

    conv.setUpdatedAt(Instant.now());
    this.repo.save(conv);

    Map<String, Object> data = this.baseMapper(conv);
    data.put(
        "messages",
        this.messageRepo.findByConversationIdOrderByCreatedAtAsc(conv.getId()).stream()
            .map(this::messageMapper)
            .toList());

    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/ai/conversations/" + conv.getId()}))
        .body(Json.of("data", data));
  }

  /** POST /api/v1/ai/conversations/{id}/messages — poursuit une conversation. */
  @PostMapping(value = {"/{id}/messages"})
  public Map<String, Object> sendMessage(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    AiConversation conv = this.findOwned(id, auth);
    String message = str(body.get("message"));
    if (message == null || message.isBlank()) {
      throw com.shelve.exception.ValidationException.single("message", "The message field is required.");
    }
    String pageContext = str(body.get("context"));
    String mode = str(body.get("mode"));
    if (mode != null && !mode.isBlank() && !mode.equals(conv.getMode())) {
      conv.setMode(mode);
      this.repo.save(conv);
    }

    // Historique (rôle + contenu) pour le contexte du LLM.
    List<Map<String, String>> history =
        this.messageRepo.findByConversationIdOrderByCreatedAtAsc(conv.getId()).stream()
            .filter(m -> m.getRole().equals("user") || m.getRole().equals("assistant"))
            .map(m -> Map.of("role", m.getRole(), "content", m.getContent()))
            .toList();

    AiMessage userMsg = new AiMessage();
    userMsg.setConversationId(conv.getId());
    userMsg.setRole("user");
    userMsg.setContent(message);
    userMsg.setContext(AiConversationController.toJson(pageContext));
    this.messageRepo.save(userMsg);

    Map<String, Object> result = this.chat.reply(message, history, pageContext, auth, conv.getMode());
    AiMessage assistantMsg = new AiMessage();
    assistantMsg.setConversationId(conv.getId());
    assistantMsg.setRole("assistant");
    assistantMsg.setContent((String) result.get("reply"));
    assistantMsg.setContext(this.serializeContext(result.get("context")));
    this.messageRepo.save(assistantMsg);

    conv.setUpdatedAt(Instant.now());
    this.repo.save(conv);

    Map<String, Object> data = this.baseMapper(conv);
    data.put(
        "messages",
        this.messageRepo.findByConversationIdOrderByCreatedAtAsc(conv.getId()).stream()
            .map(this::messageMapper)
            .toList());
    return Json.of("data", data);
  }

  /** DELETE /api/v1/ai/conversations/{id} — archive, ne supprime jamais. */
  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    AiConversation conv = this.findOwned(id, auth);
    conv.setArchivedAt(Instant.now());
    this.repo.save(conv);
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Helpers                                                            */
  /* ------------------------------------------------------------------ */

  private AiConversation findOwned(Long id, AuthenticatedUser auth) {
    return this.repo.findAll(
            (root, q, cb) ->
                cb.and(
                    cb.equal(root.get("id"), id),
                    cb.equal(root.get("userId"), auth.user().getId())))
        .stream()
        .findFirst()
        .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> baseMapper(AiConversation c) {
    Map<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", c.getId());
    map.put("title", c.getTitle());
    map.put("mode", c.getMode());
    map.put("context", AiConversationController.parseJson(c.getContext()));
    map.put("messages_count", this.messageRepo.findByConversationIdOrderByCreatedAtAsc(c.getId()).size());
    map.put("created_at", Json.timestamp(c.getCreatedAt()));
    map.put("updated_at", Json.timestamp(c.getUpdatedAt()));
    return map;
  }

  private Map<String, Object> messageMapper(AiMessage m) {
    Map<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", m.getId());
    map.put("conversation_id", m.getConversationId());
    map.put("role", m.getRole());
    map.put("content", m.getContent());
    map.put("context", AiConversationController.parseJson(m.getContext()));
    map.put("created_at", Json.timestamp(m.getCreatedAt()));
    return map;
  }

  private static Object parseJson(String json) {
    if (json == null || json.isBlank()) return null;
    try {
      return new com.fasterxml.jackson.databind.ObjectMapper().readValue(json, Object.class);
    } catch (Exception e) {
      return json;
    }
  }

  private static String toJson(Object value) {
    if (value == null) return null;
    // Une chaîne déjà JSON valide (objet/tableau) passe telle quelle.
    if (value instanceof String s && s.trim().startsWith("{") && s.trim().endsWith("}")) {
      try {
        new com.fasterxml.jackson.databind.ObjectMapper().readTree(s);
        return s;
      } catch (Exception ignored) {
        // tombe sur la sérialisation ci-dessous
      }
    }
    // Les autres valeurs (chemin, objet page) sont sérialisées en JSON.
    try {
      return new com.fasterxml.jackson.databind.ObjectMapper().writeValueAsString(value);
    } catch (Exception e) {
      return null;
    }
  }

  private String serializeContext(Object context) {
    if (context == null) return null;
    try {
      return new com.fasterxml.jackson.databind.ObjectMapper().writeValueAsString(context);
    } catch (Exception e) {
      return null;
    }
  }

  private static String titleFromMessage(String message) {
    String t = message.trim().replaceAll("\\s+", " ");
    return t.length() > 40 ? t.substring(0, 40) + "…" : (t.isEmpty() ? "Conversation" : t);
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
