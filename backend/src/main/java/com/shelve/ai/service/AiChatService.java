package com.shelve.ai.service;

import com.shelve.records.entity.Record;
import com.shelve.records.repository.RecordRepository;
import com.shelve.security.AuthenticatedUser;
import jakarta.persistence.criteria.Predicate;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.stereotype.Service;

/**
 * Moteur de conversation de l'assistant IA (panneau latéral + mention @IA du
 * chat). Construit le prompt système à partir du mode choisi, de la page
 * courante et des permissions de l'utilisateur, puis appelle Ollama.
 *
 * Périmètre d'action actuel (lecture seule) :
 *  - `search`  : rechercher des notices de l'organisation (`D10`),
 *  - `show`    : lire une notice par son id.
 * Les actions de création/modification restent à brancher (modes manuel/edit).
 */
@Service
public class AiChatService {

  private final AiService ai;
  private final RecordRepository recordRepository;

  public AiChatService(AiService ai, RecordRepository recordRepository) {
    this.ai = ai;
    this.recordRepository = recordRepository;
  }

  /**
   * Produit la réponse de l'assistant pour un message, avec l'historique.
   *
   * @param instruction message de l'utilisateur (la mention @IA retirée)
   * @param history messages précédents (role/content)
   * @param pageContext page courante (chemin + querystring)
   * @param auth utilisateur authentifié (permissions pilotées par la base)
   * @param mode manuel | edit | plan | autonome
   */
  public Map<String, Object> reply(
      String instruction,
      List<Map<String, String>> history,
      String pageContext,
      AuthenticatedUser auth,
      String mode) {
    List<AiService.ChatMessage> messages = new ArrayList<AiService.ChatMessage>();
    messages.add(new AiService.ChatMessage("system", this.systemPrompt(auth, mode, pageContext)));
    for (Map<String, String> m : history) {
      messages.add(new AiService.ChatMessage(m.get("role"), m.get("content")));
    }
    messages.add(new AiService.ChatMessage("user", instruction));

    Map<String, Object> completion = this.ai.complete(messages, true);

    String raw = String.valueOf(completion.get("content"));
    String reply;
    Map<String, Object> context = new LinkedHashMap<String, Object>();
    try {
      com.fasterxml.jackson.databind.JsonNode node =
          new com.fasterxml.jackson.databind.ObjectMapper().readTree(AiChatService.extractJson(raw));
      String action = node.path("action").asText("none");
      String actionReply = node.path("reply").asText("");
      context.put("action", action);
      if ("search".equals(action)) {
        String q = node.path("query").asText("");
        context.put("results", this.searchRecords(q, auth));
        reply = this.renderSearch(actionReply, context);
      } else if ("show".equals(action)) {
        long id = node.path("id").asLong(0);
        context.put("record", this.showRecord(id, auth));
        reply = this.renderShow(actionReply, context);
      } else {
        reply = actionReply.isBlank() ? raw : actionReply;
      }
    } catch (Exception e) {
      // JSON invalide → parsing tolérant par motifs (modèles "thinking" inclus).
      Map<String, String> fields = AiChatService.extractFields(raw);
      String action = fields.getOrDefault("action", "none");
      String actionReply = fields.getOrDefault("reply", "");
      context.put("action", action);
      if ("search".equals(action)) {
        String q = fields.getOrDefault("query", "");
        context.put("results", this.searchRecords(q, auth));
        reply = this.renderSearch(actionReply, context);
      } else if ("show".equals(action)) {
        long id = 0;
        try {
          id = Long.parseLong(fields.getOrDefault("id", "0"));
        } catch (NumberFormatException ignored) {
          // id invalide → aucune notice
        }
        context.put("record", this.showRecord(id, auth));
        reply = this.renderShow(actionReply, context);
      } else {
        reply = actionReply.isBlank() ? raw : actionReply;
      }
    }

    Map<String, Object> result = new LinkedHashMap<String, Object>();
    result.put("success", true);
    result.put("reply", reply);
    result.put("context", context);
    return result;
  }

  /* ------------------------------------------------------------------ */
  /*  Prompt système                                                     */
  /* ------------------------------------------------------------------ */

  /** Extrait le JSON de la réponse (retire `<think>`, blocs de code et texte parasite). */
  static String extractJson(String raw) {
    if (raw == null) return "{}";
    String cleaned = raw
        .replaceAll("(?s)<think>.*?</think>", "")
        .replaceAll("(?s)```json\\s*", "")
        .replaceAll("(?s)```\\s*$", "")
        .trim();
    int start = cleaned.indexOf('{');
    int end = cleaned.lastIndexOf('}');
    if (start >= 0 && end > start) {
      return cleaned.substring(start, end + 1);
    }
    return cleaned;
  }

  /** Extraction tolérante des champs JSON quand le JSON est malformé (modèles "thinking"). */
  static Map<String, String> extractFields(String raw) {
    Map<String, String> fields = new LinkedHashMap<String, String>();
    if (raw == null) return fields;
    String cleaned = raw.replaceAll("(?s)<think>.*?</think>", "");
    java.util.regex.Pattern p =
        java.util.regex.Pattern.compile("\"(action|reply|query|id)\"\\s*[:=]\\s*\"?([^\",}\\]]+)");
    java.util.regex.Matcher m = p.matcher(cleaned);
    while (m.find()) {
      String key = m.group(1);
      String value = m.group(2).trim();
      if (!fields.containsKey(key)) fields.put(key, value);
    }
    return fields;
  }

  private String systemPrompt(AuthenticatedUser auth, String mode, String pageContext) {
    String modeRule =
        switch (mode == null ? "manuel" : mode) {
          case "autonome" ->
              "Mode autonome : vous pouvez agir directement, dans la limite stricte des permissions listées ci-dessous.";
          case "edit" ->
              "Mode edit : les modifications sont pré-approuvées ; la création et la suppression restent soumises à confirmation.";
          case "plan" ->
              "Mode plan : ne produisez JAMAIS d'action — seulement un plan à valider.";
          default ->
              "Mode manuel : demandez toujours confirmation avant toute action (création, modification, suppression).";
        };

        return """
            Vous êtes l'assistant IA de SHELVE, un système de gestion d'archives (notices, plan de classement).
            Vous répondez en français, de façon concise et utile.

            SORTIE OBLIGATOIRE : répondez UNIQUEMENT avec un objet JSON, sans texte avant, sans texte après, sans marqueurs ```json ni ```. Schéma strict :
            {"reply": "votre réponse à l'utilisateur", "action": "none", "query": "", "id": 0}
            - "action" ne peut valoir QUE : "none", "search", "show".
            - action "search" : mettez "query" avec les mots-clés à chercher (et "reply" bref).
            - action "show" : mettez "id" avec l'identifiant numérique de la notice.
            - action "none" : réponse simple, "query" vide, "id" 0.

            Périmètre d'action autorisé :
            - action "search" : rechercher des notices avec le champ "query" (mots-clés) — utilisez-la dès qu'on vous demande de trouver/chercher/lister des documents ou archives.
            - action "show" : lire le détail d'une notice avec le champ "id" (identifiant numérique).
            - action "none" : réponse simple sans action (question générale, mode plan, etc.).
            Vous n'avez AUCUNE autre action : jamais de création, modification ou suppression.

            Contexte de la page : %s

            %s

            Permissions de l'utilisateur (%s) :
            %s
            """
        .formatted(
            pageContext == null || pageContext.isBlank() ? "/" : pageContext,
            modeRule,
            auth.user().getName(),
            this.permissionList(auth));
  }

  private String permissionList(AuthenticatedUser auth) {
    List<String> perms = auth.permissions();
    if (perms == null || perms.isEmpty()) {
      return "Aucune permission spécifique.";
    }
    // Résumé compact : nombre total + ressources couvertes (préfixes uniques).
    java.util.TreeSet<String> prefixes = new java.util.TreeSet<String>();
    for (String p : perms) {
      int idx = p.lastIndexOf('_');
      prefixes.add(idx > 0 ? p.substring(0, idx) : p);
    }
    return "L'utilisateur dispose de " + perms.size() + " permissions couvrant les ressources suivantes : "
        + String.join(", ", prefixes)
        + ". Ces permissions sont fournies à titre informatif : vos actions restent limitées à search/show (lecture seule).";
  }

  /* ------------------------------------------------------------------ */
  /*  Outils (lecture seule)                                             */
  /* ------------------------------------------------------------------ */

  private List<Map<String, Object>> searchRecords(String q, AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    Specification<Record> spec = (root, query, cb) -> {
      List<Predicate> preds = new ArrayList<Predicate>();
      preds.add(cb.equal(root.get("organisationId"), orgId));
      preds.add(cb.equal(root.get("isCurrentVersion"), true));
      if (q != null && !q.isBlank()) {
        for (String term : q.trim().split("\\s+")) {
          String like = "%" + term.toLowerCase() + "%";
          preds.add(cb.or(
              cb.like(cb.lower(root.get("name")), like),
              cb.like(cb.lower(root.get("code")), like),
              cb.like(cb.lower(root.get("description")), like)));
        }
      }
      return cb.and(preds.toArray(new Predicate[0]));
    };
    return this.recordRepository
        .findAll(spec, PageRequest.of(0, 10, Sort.by(Sort.Direction.DESC, "updatedAt")))
        .stream()
        .map(this::briefRecord)
        .toList();
  }

  private Map<String, Object> showRecord(long id, AuthenticatedUser auth) {
    return this.recordRepository
        .findById(id)
        .filter(r -> r.getOrganisationId().equals(auth.user().getCurrentOrganisationId()))
        .map(this::briefRecord)
        .orElse(null);
  }

  private Map<String, Object> briefRecord(Record r) {
    Map<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("description", r.getDescription());
    map.put("type_id", r.getTypeId());
    map.put("level_id", r.getLevelId());
    map.put("status_id", r.getStatusId());
    map.put("date_exact", r.getDateExact() != null ? r.getDateExact().toString() : null);
    map.put("updated_at", com.shelve.common.Json.timestamp(r.getUpdatedAt()));
    return map;
  }

  private String renderSearch(String actionReply, Map<String, Object> context) {
    @SuppressWarnings("unchecked")
    List<Map<String, Object>> results = (List<Map<String, Object>>) context.get("results");
    StringBuilder sb = new StringBuilder();
    if (actionReply != null && !actionReply.isBlank()) {
      sb.append(actionReply).append("\n\n");
    }
    if (results == null || results.isEmpty()) {
      sb.append("Aucune notice trouvée.");
    } else {
      sb.append("J'ai trouvé ").append(results.size()).append(" notice(s) :\n");
      for (Map<String, Object> r : results) {
        sb.append("- [").append(r.get("code")).append("] ").append(r.get("name"))
            .append(" (id ").append(r.get("id")).append(")\n");
      }
      sb.append("\nDemandez-moi de lire le détail d'une notice en citant son id.");
    }
    return sb.toString();
  }

  private String renderShow(String actionReply, Map<String, Object> context) {
    @SuppressWarnings("unchecked")
    Map<String, Object> record = (Map<String, Object>) context.get("record");
    StringBuilder sb = new StringBuilder();
    if (actionReply != null && !actionReply.isBlank()) {
      sb.append(actionReply).append("\n\n");
    }
    if (record == null) {
      sb.append("Notice introuvable (ou hors de votre organisation).");
    } else {
      sb.append("Notice : ").append(record.get("name")).append(" [").append(record.get("code")).append("]\n");
      sb.append("- Description : ").append(record.get("description") == null ? "—" : record.get("description")).append("\n");
      sb.append("- Date exacte : ").append(record.get("date_exact") == null ? "—" : record.get("date_exact")).append("\n");
      sb.append("- Dernière mise à jour : ").append(record.get("updated_at") == null ? "—" : record.get("updated_at"));
    }
    return sb.toString();
  }
}
