package com.shelve.collaboration.controller;

import com.shelve.collaboration.entity.Workplace;
import com.shelve.collaboration.entity.WorkplaceConversation;
import com.shelve.collaboration.entity.WorkplaceConversationParticipant;
import com.shelve.collaboration.entity.WorkplaceMessage;
import com.shelve.collaboration.repository.WorkplaceConversationParticipantRepository;
import com.shelve.collaboration.repository.WorkplaceConversationRepository;
import com.shelve.collaboration.repository.WorkplaceMessageRepository;
import com.shelve.collaboration.repository.WorkplaceRepository;
import com.shelve.common.Json;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.common.Validator;
import com.shelve.exception.ApiException;
import com.shelve.exception.ValidationException;
import com.shelve.organisation.entity.User;
import com.shelve.organisation.repository.UserRepository;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.persistence.criteria.Predicate;
import jakarta.persistence.criteria.Root;
import jakarta.persistence.criteria.Subquery;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.time.Instant;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.multipart.MultipartFile;

@RestController
@RequestMapping(value = {"/api/v1/workplace-conversations"})
public class WorkplaceConversationController {
  /** Racine de stockage des pièces jointes de chat. */
  private static final String STORAGE_ROOT =
      "C:/wamp64_New/www/archives/shelve/backend/storage/chat";
  private static final List<String> FILTERABLE =
      List.of("id", "workplace_id", "type", "name", "created_by", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE =
      List.of("creator", "participants.user", "workplace", "messages.user");

  private final WorkplaceConversationRepository repo;
  private final WorkplaceConversationParticipantRepository participantRepo;
  private final WorkplaceMessageRepository messageRepo;
  private final WorkplaceRepository workplaceRepo;
  private final UserRepository userRepo;

  public WorkplaceConversationController(
      WorkplaceConversationRepository repo,
      WorkplaceConversationParticipantRepository participantRepo,
      WorkplaceMessageRepository messageRepo,
      WorkplaceRepository workplaceRepo,
      UserRepository userRepo) {
    this.repo = repo;
    this.participantRepo = participantRepo;
    this.messageRepo = messageRepo;
    this.workplaceRepo = workplaceRepo;
    this.userRepo = userRepo;
  }

  /** GET /api/v1/workplace-conversations */
  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Set<String> includes = Set.copyOf(qp.getIncludes());

    // Isolation : un workplace hors organisation courante est 404.
    Object rawWorkplaceId = qp.getFilters().get("workplace_id") == null
        ? null
        : qp.getFilters().get("workplace_id").get("eq");
    if (rawWorkplaceId != null) {
      this.workplaceInScope(Long.valueOf(String.valueOf(rawWorkplaceId)), auth);
    }

    Specification<WorkplaceConversation> spec =
        this.participantScope(auth.user().getId());
    if (rawWorkplaceId != null) {
      Long wid = Long.valueOf(String.valueOf(rawWorkplaceId));
      spec = spec.and((root, q, cb) -> cb.equal(root.get("workplaceId"), wid));
    }

    PageRequest pageable =
        PageRequest.of(
            qp.getPageNumber() - 1,
            qp.getPageSize(),
            this.sortOf(qp));
    Page<WorkplaceConversation> page = this.repo.findAll(spec, (Pageable) pageable);
    List<WorkplaceConversation> items = page.getContent();

    // Chargement en lots des relations, selon l'include demandé.
    Map<Long, Workplace> workplaces = this.loadWorkplaces(items);
    Map<Long, List<WorkplaceConversationParticipant>> participants = this.loadParticipants(items);
    Map<Long, List<WorkplaceMessage>> messages =
        includes.contains("messages.user") || includes.contains("messages")
            ? this.loadMessages(items)
            : Map.of();
    Map<Long, User> users = this.loadUsers(participants, messages);

    List<Map<String, Object>> data =
        items.stream()
            .map(
                c ->
                    this.mapper(
                        c,
                        includes,
                        workplaces.get(c.getId()),
                        participants.get(c.getId()),
                        messages.get(c.getId()),
                        users))
            .toList();

    return Paging.envelope(
        data,
        page.getTotalElements(),
        qp.getPageNumber(),
        qp.getPageSize(),
        request);
  }

  /** GET /api/v1/workplace-conversations/{id} */
  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_view");
    WorkplaceConversation conv = this.find(id, auth);
    this.assertParticipant(conv.getId(), auth.user().getId());

    // Marque la conversation lue pour l'utilisateur courant.
    this.participantRepo
        .findByConversationId(conv.getId()).stream()
        .filter(p -> p.getUserId().equals(auth.user().getId()))
        .findFirst()
        .ifPresent(
            p -> {
              p.setLastReadAt(Instant.now());
              this.participantRepo.save(p);
            });

    Set<String> includes = Set.of("creator", "participants.user", "messages.user", "workplace");
    Workplace workplace =
        conv.getWorkplaceId() != null
            ? this.workplaceRepo.findById(conv.getWorkplaceId()).orElse(null)
            : null;
    List<WorkplaceConversationParticipant> participants =
        this.participantRepo.findByConversationId(conv.getId());
    List<WorkplaceMessage> messages =
        this.messageRepo.findByConversationIdOrderByCreatedAtAsc(conv.getId());
    Map<Long, User> users = this.loadUsers(Map.of(conv.getId(), participants), Map.of(conv.getId(), messages));
    return Json.of("data", this.mapper(conv, includes, workplace, participants, messages, users));
  }

  /** POST /api/v1/workplace-conversations */
  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_create");

    String type = str(body.get("type"));
    if (type == null) {
      throw ValidationException.single("type", "The type field is required.");
    }
    if (!List.of("private", "group", "channel").contains(type)) {
      throw ValidationException.single(
          "type", "The selected type is invalid. Valid types : private, group, channel.");
    }
    boolean isPrivate = type.equals("private");
    String name = str(body.get("name"));
    if (!isPrivate && name == null) {
      throw ValidationException.single("name", "The name field is required.");
    }

    Long workplaceId = longOf(body.get("workplace_id"));
    if (workplaceId != null) {
      this.workplaceInScope(workplaceId, auth);
    }

    List<Long> participantIds = longList(body.get("participant_ids"));

    // Conversation privée : réutiliser une existante entre les deux membres.
    if (isPrivate && participantIds.size() == 1) {
      WorkplaceConversation existing =
          this.findExistingPrivate(workplaceId, auth.user().getId(), participantIds.get(0));
      if (existing != null) {
        return ResponseEntity.status(200).body(this.show(existing.getId()));
      }
    }

    WorkplaceConversation conv = new WorkplaceConversation();
    conv.setWorkplaceId(workplaceId);
    conv.setType(type);
    conv.setName(isPrivate ? null : name);
    conv.setCreatedBy(auth.user().getId());
    this.repo.save(conv);

    Set<Long> memberIds = new java.util.HashSet<Long>();
    memberIds.add(auth.user().getId());
    memberIds.addAll(participantIds);
    for (Long userId : memberIds) {
      WorkplaceConversationParticipant p = new WorkplaceConversationParticipant();
      p.setConversationId(conv.getId());
      p.setUserId(userId);
      p.setRole(userId.equals(auth.user().getId()) ? "owner" : "member");
      this.participantRepo.save(p);
    }

    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/workplace-conversations/" + conv.getId()}))
        .body(this.show(conv.getId()));
  }

  /** PATCH /api/v1/workplace-conversations/{id} — créateur seul. */
  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(
      @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_update");
    WorkplaceConversation conv = this.find(id, auth);
    if (!conv.getCreatedBy().equals(auth.user().getId())) {
      throw new ApiException(org.springframework.http.HttpStatus.FORBIDDEN,
          "Seul le créateur de la conversation peut la modifier.");
    }
    if (body.containsKey("name")) {
      conv.setName(str(body.get("name")));
    }
    if (body.containsKey("description")) {
      conv.setDescription(str(body.get("description")));
    }
    this.repo.save(conv);
    return Json.of("data", this.show(conv.getId()));
  }

  /** DELETE /api/v1/workplace-conversations/{id} — créateur seul. */
  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_delete");
    WorkplaceConversation conv = this.find(id, auth);
    if (!conv.getCreatedBy().equals(auth.user().getId())) {
      throw new ApiException(org.springframework.http.HttpStatus.FORBIDDEN,
          "Seul le créateur de la conversation peut la supprimer.");
    }
    this.repo.delete(conv);
    return ResponseEntity.noContent().build();
  }

  /** POST /api/v1/workplace-conversations/{id}/messages */
  @PostMapping(value = {"/{id}/messages"})
  public ResponseEntity<Map<String, Object>> storeMessage(
      @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_view");
    WorkplaceConversation conv = this.find(id, auth);
    this.assertParticipant(conv.getId(), auth.user().getId());

    String content = str(body.get("content"));
    if (content == null || content.isBlank()) {
      throw ValidationException.single("content", "The content field is required.");
    }

    WorkplaceMessage message = new WorkplaceMessage();
    message.setConversationId(conv.getId());
    message.setUserId(auth.user().getId());
    message.setContent(content);
    this.messageRepo.save(message);

    // Touche la conversation pour remonter le fil dans la liste.
    conv.setUpdatedAt(Instant.now());
    this.repo.save(conv);

    Map<Long, User> users =
        this.userRepo.findAllById(List.of(auth.user().getId())).stream()
            .collect(Collectors.toMap(User::getId, Function.identity()));
    Map<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("id", message.getId());
    data.put("conversation_id", message.getConversationId());
    data.put("user_id", message.getUserId());
    data.put("content", message.getContent());
    data.put("user", this.briefUser(users.get(message.getUserId())));
    data.put("created_at", Json.timestamp(message.getCreatedAt()));
    data.put("updated_at", Json.timestamp(message.getUpdatedAt()));
    data.put("deleted_at", null);

    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/workplace-conversations/"
                          + conv.getId()
                          + "/messages/"
                          + message.getId()
                    }))
        .body(Json.of("data", data));
  }

  /** POST /api/v1/workplace-conversations/{id}/messages/attachment — multipart. */
  @PostMapping(value = {"/{id}/messages/attachment"})
  public ResponseEntity<Map<String, Object>> storeAttachment(
      @PathVariable Long id,
      @RequestParam("file") MultipartFile file,
      @RequestParam(value = "content", required = false) String content,
      HttpServletRequest request) throws IOException {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_view");
    WorkplaceConversation conv = this.find(id, auth);
    this.assertParticipant(conv.getId(), auth.user().getId());

    if (file.isEmpty()) {
      throw ValidationException.single("file", "The file field is required.");
    }

    String original = file.getOriginalFilename();
    String safeName = original != null && !original.isBlank() ? original : "piece-jointe";
    String ext = safeName.contains(".")
        ? safeName.substring(safeName.lastIndexOf('.') + 1).toLowerCase()
        : "";
    String stored = System.currentTimeMillis() + "-" + Math.abs(safeName.hashCode()) + (ext.isEmpty() ? "" : "." + ext);

    Path dir = this.chatStorageDir(conv.getId());
    Files.createDirectories(dir);
    Path target = dir.resolve(stored);
    file.transferTo(target);

    WorkplaceMessage message = new WorkplaceMessage();
    message.setConversationId(conv.getId());
    message.setUserId(auth.user().getId());
    message.setContent(content == null ? "" : content);
    message.setAttachmentName(safeName);
    message.setAttachmentPath(target.toString());
    message.setAttachmentMime(file.getContentType());
    message.setAttachmentSize(file.getSize());
    this.messageRepo.save(message);

    // Touche la conversation pour remonter le fil dans la liste.
    conv.setUpdatedAt(Instant.now());
    this.repo.save(conv);

    Map<Long, User> users =
        this.userRepo.findAllById(List.of(auth.user().getId())).stream()
            .collect(Collectors.toMap(User::getId, Function.identity()));

    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/workplace-conversations/"
                          + conv.getId()
                          + "/messages/"
                          + message.getId()
                    }))
        .body(Json.of("data", this.messageMapper(message, users.get(message.getUserId()))));
  }

  /** GET /api/v1/workplace-conversations/{id}/messages/{messageId}/attachment */
  @GetMapping(value = {"/{id}/messages/{messageId}/attachment"})
  public void downloadAttachment(
      @PathVariable Long id, @PathVariable Long messageId, HttpServletResponse response)
      throws IOException {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "workplace_conversation_view");
    WorkplaceConversation conv = this.find(id, auth);
    this.assertParticipant(conv.getId(), auth.user().getId());

    WorkplaceMessage message =
        this.messageRepo.findById(messageId).orElseThrow(() -> ApiException.notFound());
    if (!message.getConversationId().equals(conv.getId())
        || message.getAttachmentPath() == null) {
      throw ApiException.notFound();
    }

    Path path = Path.of(message.getAttachmentPath());
    if (!Files.isRegularFile(path)) {
      throw ApiException.notFound();
    }

    response.setContentType(
        message.getAttachmentMime() != null ? message.getAttachmentMime() : "application/octet-stream");
    response.setHeader(
        "Content-Disposition",
        "inline; filename=\"" + message.getAttachmentName() + "\"");
    Files.copy(path, response.getOutputStream());
  }

  private Path chatStorageDir(Long conversationId) {
    return Path.of(STORAGE_ROOT, "chat", String.valueOf(conversationId));
  }

  /* ------------------------------------------------------------------ */
  /*  Requêtes et chargements en lots                                    */
  /* ------------------------------------------------------------------ */

  private WorkplaceConversation find(Long id, AuthenticatedUser auth) {
    return this.repo
        .findById(id)
        .orElseThrow(() -> ApiException.notFound());
  }

  private void assertParticipant(Long conversationId, Long userId) {
    boolean isParticipant =
        this.participantRepo.findByConversationId(conversationId).stream()
            .anyMatch(p -> p.getUserId().equals(userId));
    if (!isParticipant) {
      throw new ApiException(
          org.springframework.http.HttpStatus.FORBIDDEN,
          "Cette conversation ne vous appartient pas.");
    }
  }

  /** Scope : seules les conversations dont l'utilisateur est participant. */
  private Specification<WorkplaceConversation> participantScope(Long userId) {
    return (root, query, cb) -> {
      Subquery<Long> sub = query.subquery(Long.class);
      Root<WorkplaceConversationParticipant> p = sub.from(WorkplaceConversationParticipant.class);
      sub.select(p.get("conversationId"));
      sub.where(cb.equal(p.get("userId"), userId));
      return cb.in(root.get("id")).value(sub);
    };
  }

  private Workplace workplaceInScope(Long id, AuthenticatedUser auth) {
    Workplace w =
        this.workplaceRepo.findById(id).orElseThrow(() -> ApiException.notFound());
    if (w.getOrganisationId() == null
        || !w.getOrganisationId().equals(auth.user().getCurrentOrganisationId())) {
      throw ApiException.notFound();
    }
    return w;
  }

  /** Conversation privée existante entre deux membres (même workplace ou null). */
  private WorkplaceConversation findExistingPrivate(Long workplaceId, Long me, Long other) {
    List<Long> myConversationIds =
        this.participantRepo.findByUserId(me).stream()
            .map(WorkplaceConversationParticipant::getConversationId)
            .toList();
    List<Long> theirConversationIds =
        this.participantRepo.findByUserId(other).stream()
            .map(WorkplaceConversationParticipant::getConversationId)
            .toList();
    Set<Long> common = new java.util.HashSet<Long>(myConversationIds);
    common.retainAll(theirConversationIds);
    if (common.isEmpty()) return null;

    for (Long cid : common) {
      WorkplaceConversation conv =
          this.repo.findById(cid).orElse(null);
      if (conv == null || !conv.getType().equals("private")) continue;
      boolean sameScope =
          (workplaceId == null && conv.getWorkplaceId() == null)
              || (workplaceId != null && workplaceId.equals(conv.getWorkplaceId()));
      if (!sameScope) continue;
      List<Long> memberIds =
          this.participantRepo.findByConversationId(cid).stream()
              .map(WorkplaceConversationParticipant::getUserId)
              .toList();
      if (memberIds.size() == 2 && memberIds.contains(me) && memberIds.contains(other)) {
        return conv;
      }
    }
    return null;
  }

  private Sort sortOf(QueryParams qp) {
    if (qp.getSort().isEmpty()) {
      return Sort.by(Sort.Direction.DESC, "updatedAt");
    }
    QueryParams.SortField sf = qp.getSort().get(0);
    return Sort.by(
        sf.descending() ? Sort.Direction.DESC : Sort.Direction.ASC,
        toAttribute(sf.field()));
  }

  private Map<Long, Workplace> loadWorkplaces(List<WorkplaceConversation> items) {
    List<Long> ids =
        items.stream()
            .map(WorkplaceConversation::getWorkplaceId)
            .filter(java.util.Objects::nonNull)
            .distinct()
            .toList();
    if (ids.isEmpty()) return Map.of();
    return this.workplaceRepo.findAllById(ids).stream()
        .collect(Collectors.toMap(Workplace::getId, Function.identity()));
  }

  private Map<Long, List<WorkplaceConversationParticipant>> loadParticipants(
      List<WorkplaceConversation> items) {
    List<Long> ids = items.stream().map(WorkplaceConversation::getId).toList();
    if (ids.isEmpty()) return Map.of();
    return this.participantRepo.findByConversationIdIn(ids).stream()
        .collect(Collectors.groupingBy(WorkplaceConversationParticipant::getConversationId));
  }

  private Map<Long, List<WorkplaceMessage>> loadMessages(List<WorkplaceConversation> items) {
    List<Long> ids = items.stream().map(WorkplaceConversation::getId).toList();
    if (ids.isEmpty()) return Map.of();
    return this.messageRepo.findByConversationIdInOrderByCreatedAtAsc(ids).stream()
        .collect(Collectors.groupingBy(WorkplaceMessage::getConversationId));
  }

  private Map<Long, User> loadUsers(
      Map<Long, List<WorkplaceConversationParticipant>> participants,
      Map<Long, List<WorkplaceMessage>> messages) {
    Set<Long> ids = new java.util.HashSet<Long>();
    participants.values().forEach(list -> list.forEach(p -> ids.add(p.getUserId())));
    messages.values().forEach(list -> list.forEach(m -> ids.add(m.getUserId())));
    if (ids.isEmpty()) return Map.of();
    return this.userRepo.findAllById(ids).stream()
        .collect(Collectors.toMap(User::getId, Function.identity()));
  }

  /* ------------------------------------------------------------------ */
  /*  Mappers                                                            */
  /* ------------------------------------------------------------------ */

  private Map<String, Object> mapper(
      WorkplaceConversation c,
      Set<String> includes,
      Workplace workplace,
      List<WorkplaceConversationParticipant> participants,
      List<WorkplaceMessage> messages,
      Map<Long, User> users) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", c.getId());
    map.put("workplace_id", c.getWorkplaceId());
    map.put("type", c.getType());
    map.put("name", c.getName());
    map.put("description", c.getDescription());
    map.put("created_by", c.getCreatedBy());
    map.put(
        "participants",
        participants == null
            ? List.of()
            : participants.stream()
                .map(p -> this.participantMapper(p, users.get(p.getUserId())))
                .toList());
    map.put(
        "messages",
        messages == null
            ? List.of()
            : messages.stream()
                .map(m -> this.messageMapper(m, users.get(m.getUserId())))
                .toList());
    if (includes.contains("workplace") && workplace != null) {
      map.put("workplace", this.briefWorkplace(workplace));
    }
    if (includes.contains("creator") && c.getCreatedBy() != null) {
      map.put("creator", this.briefUser(users.get(c.getCreatedBy())));
    }
    map.put("created_at", Json.timestamp(c.getCreatedAt()));
    map.put("updated_at", Json.timestamp(c.getUpdatedAt()));
    map.put("deleted_at", null);
    return map;
  }

  private Map<String, Object> participantMapper(
      WorkplaceConversationParticipant p, User user) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", p.getId());
    map.put("conversation_id", p.getConversationId());
    map.put("user_id", p.getUserId());
    map.put("role", p.getRole());
    map.put("last_read_at", Json.timestamp(p.getLastReadAt()));
    map.put("user", this.briefUser(user));
    return map;
  }

  private Map<String, Object> messageMapper(WorkplaceMessage m, User user) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", m.getId());
    map.put("conversation_id", m.getConversationId());
    map.put("user_id", m.getUserId());
    map.put("content", m.getContent());
    map.put("attachment_name", m.getAttachmentName());
    map.put("attachment_mime", m.getAttachmentMime());
    map.put("attachment_size", m.getAttachmentSize());
    map.put("user", this.briefUser(user));
    map.put("created_at", Json.timestamp(m.getCreatedAt()));
    map.put("updated_at", Json.timestamp(m.getUpdatedAt()));
    map.put("deleted_at", null);
    return map;
  }

  private Map<String, Object> briefWorkplace(Workplace w) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", w.getId());
    map.put("code", w.getCode());
    map.put("name", w.getName());
    return map;
  }

  private Map<String, Object> briefUser(User u) {
    if (u == null) return null;
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", u.getId());
    map.put("name", u.getName());
    map.put("surname", u.getSurname());
    map.put("email", u.getEmail());
    return map;
  }

  private static String toAttribute(String field) {
    if (!field.contains("_")) return field;
    StringBuilder sb = new StringBuilder();
    String[] parts = field.split("_");
    sb.append(parts[0]);
    for (int i = 1; i < parts.length; i++) {
      if (parts[i].isEmpty()) continue;
      sb.append(Character.toUpperCase(parts[i].charAt(0))).append(parts[i].substring(1));
    }
    return sb.toString();
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Long longOf(Object value) {
    if (value == null) return null;
    try {
      return ((Number) value).longValue();
    } catch (ClassCastException e) {
      try {
        return Long.parseLong(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }

  private static List<Long> longList(Object value) {
    List<Long> out = new ArrayList<Long>();
    if (value instanceof List<?> list) {
      for (Object item : list) {
        Long id = longOf(item);
        if (id != null) out.add(id);
      }
    }
    return out;
  }
}
