package com.shelve.ai.service;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.shelve.ai.config.AiProperties;
import com.shelve.ai.config.AiProviderConfig;
import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.stereotype.Service;

/**
 * Client LLM universel — remplace l'ancien OllamaService.
 *
 * Prend en charge les familles de providers configurables :
 *  - `openai`   : API compatible OpenAI — Ollama, OpenAI, OpenRouter, LMStudio,
 *                 Mistral, Grok, providers « custom » (endpoint /chat/completions) ;
 *  - `anthropic`: API Claude (endpoint /messages) ;
 *  - `gemini`   : API Google Gemini (endpoint :generateContent).
 *
 * Le provider utilisé est résolu par `AiProviderRegistry` (défaut `app.ai.default-provider`,
 * surchargeable par la table `settings` / variables d'environnement).
 */
@Service
public class AiService {

  public record ChatMessage(String role, String content) {}

  private final AiProperties props;
  private final AiProviderRegistry registry;
  private final com.shelve.settings.service.SettingReader settings;
  private final ObjectMapper mapper;
  private final HttpClient client;

  public AiService(
      AiProperties props,
      AiProviderRegistry registry,
      com.shelve.settings.service.SettingReader settings,
      ObjectMapper mapper) {
    this.props = props;
    this.registry = registry;
    this.settings = settings;
    this.mapper = mapper;
    this.client = HttpClient.newBuilder().connectTimeout(Duration.ofSeconds(15)).build();
  }

  /** Timeout effectif : `ai_request_timeout` (settings) sinon `app.ai.timeout-seconds`. */
  private Duration timeout() {
    Object configured = this.settings.effective("ai_request_timeout");
    if (configured != null) {
      try {
        return Duration.ofSeconds(Long.parseLong(String.valueOf(configured)));
      } catch (NumberFormatException ignored) {
        // défaut ci-dessous
      }
    }
    return Duration.ofSeconds(this.props.getTimeoutSeconds());
  }

  /** Génère la réponse de l'assistant pour une liste de messages, sur le provider par défaut. */
  public Map<String, Object> complete(List<ChatMessage> messages, boolean jsonMode) {
    return this.complete(this.registry.resolveDefault(), messages, jsonMode);
  }

  /** Génère la réponse avec un provider explicitement nommé. */
  public Map<String, Object> complete(String providerName, List<ChatMessage> messages, boolean jsonMode) {
    return this.complete(this.registry.resolve(providerName), messages, jsonMode);
  }

  public Map<String, Object> complete(AiProviderConfig provider, List<ChatMessage> messages, boolean jsonMode) {
    return switch (provider.getType()) {
      case AiProviderConfig.TYPE_ANTHROPIC -> this.completeAnthropic(provider, messages, jsonMode);
      case AiProviderConfig.TYPE_GEMINI -> this.completeGemini(provider, messages, jsonMode);
      default -> this.completeOpenAiCompatible(provider, messages, jsonMode);
    };
  }

  /* ------------------------------------------------------------------ */
  /*  OpenAI-compatible (Ollama, OpenAI, OpenRouter, LMStudio, …)        */
  /* ------------------------------------------------------------------ */

  private Map<String, Object> completeOpenAiCompatible(
      AiProviderConfig provider, List<ChatMessage> messages, boolean jsonMode) {
    try {
      Map<String, Object> body = new LinkedHashMap<String, Object>();
      body.put("model", provider.getModel());
      body.put("messages", messages.stream().map(m -> {
        Map<String, Object> msg = new LinkedHashMap<String, Object>();
        msg.put("role", m.role());
        msg.put("content", m.content());
        return msg;
      }).toList());
      body.put("stream", false);
      body.put("max_tokens", 1024);
      if (jsonMode) {
        body.put("format", "json");
      }

      HttpRequest.Builder req =
          HttpRequest.newBuilder(this.url(provider, "/chat/completions"))
              .header("Content-Type", "application/json")
              .timeout(this.timeout())
              .POST(HttpRequest.BodyPublishers.ofString(this.mapper.writeValueAsString(body)));
      if (provider.getApiKey() != null && !provider.getApiKey().isBlank()) {
        req.header("Authorization", "Bearer " + provider.getApiKey());
      }

      HttpResponse<String> response = this.client.send(req.build(), HttpResponse.BodyHandlers.ofString());
      JsonNode root = this.mapper.readTree(response.body());
      JsonNode contentNode = root.path("choices").path(0).path("message").path("content");
      return this.result(contentNode, root);
    } catch (IOException | InterruptedException e) {
      throw new RuntimeException(
          "Échec de l'appel au provider " + provider.getType() + " (" + provider.getModel() + ") : " + e.getMessage(), e);
    }
  }

  /* ------------------------------------------------------------------ */
  /*  Anthropic (Claude)                                                 */
  /* ------------------------------------------------------------------ */

  private Map<String, Object> completeAnthropic(
      AiProviderConfig provider, List<ChatMessage> messages, boolean jsonMode) {
    try {
      List<Map<String, Object>> content = new ArrayList<Map<String, Object>>();
      String system = "";
      for (ChatMessage m : messages) {
        if ("system".equals(m.role())) {
          system = m.content();
          continue;
        }
        Map<String, Object> msg = new LinkedHashMap<String, Object>();
        msg.put("role", "user".equals(m.role()) ? "user" : "assistant");
        msg.put("content", List.of(Map.of("type", "text", "text", m.content())));
        content.add(msg);
      }

      Map<String, Object> body = new LinkedHashMap<String, Object>();
      body.put("model", provider.getModel());
      body.put("max_tokens", 2048);
      if (!system.isBlank()) body.put("system", system);
      body.put("messages", content);

      HttpRequest.Builder req =
          HttpRequest.newBuilder(this.url(provider, "/messages"))
              .header("Content-Type", "application/json")
              .header("x-api-key", provider.getApiKey() == null ? "" : provider.getApiKey())
              .header("anthropic-version", "2023-06-01")
              .timeout(this.timeout())
              .POST(HttpRequest.BodyPublishers.ofString(this.mapper.writeValueAsString(body)));

      HttpResponse<String> response = this.client.send(req.build(), HttpResponse.BodyHandlers.ofString());
      JsonNode root = this.mapper.readTree(response.body());
      JsonNode contentNode = root.path("content").path(0).path("text");
      return this.result(contentNode, root);
    } catch (IOException | InterruptedException e) {
      throw new RuntimeException(
          "Échec de l'appel au provider anthropic (" + provider.getModel() + ") : " + e.getMessage(), e);
    }
  }

  /* ------------------------------------------------------------------ */
  /*  Gemini                                                             */
  /* ------------------------------------------------------------------ */

  private Map<String, Object> completeGemini(
      AiProviderConfig provider, List<ChatMessage> messages, boolean jsonMode) {
    try {
      List<Map<String, Object>> contents = new ArrayList<Map<String, Object>>();
      for (ChatMessage m : messages) {
        if ("system".equals(m.role())) continue;
        Map<String, Object> msg = new LinkedHashMap<String, Object>();
        msg.put("role", "user".equals(m.role()) ? "user" : "model");
        msg.put("parts", List.of(Map.of("text", m.content())));
        contents.add(msg);
      }

      Map<String, Object> body = new LinkedHashMap<String, Object>();
      body.put("contents", contents);
      body.put(
          "generationConfig",
          Map.of("responseMimeType", jsonMode ? "application/json" : "text/plain"));

      String endpoint = "/models/" + provider.getModel() + ":generateContent";
      HttpRequest.Builder req =
          HttpRequest.newBuilder(this.url(provider, endpoint))
              .header("Content-Type", "application/json")
              .timeout(this.timeout())
              .POST(HttpRequest.BodyPublishers.ofString(this.mapper.writeValueAsString(body)));
      if (provider.getApiKey() != null && !provider.getApiKey().isBlank()) {
        req.header("x-goog-api-key", provider.getApiKey());
      }

      HttpResponse<String> response = this.client.send(req.build(), HttpResponse.BodyHandlers.ofString());
      JsonNode root = this.mapper.readTree(response.body());
      JsonNode contentNode = root.path("candidates").path(0).path("content").path("parts").path(0).path("text");
      return this.result(contentNode, root);
    } catch (IOException | InterruptedException e) {
      throw new RuntimeException(
          "Échec de l'appel au provider gemini (" + provider.getModel() + ") : " + e.getMessage(), e);
    }
  }

  /* ------------------------------------------------------------------ */
  /*  Helpers                                                            */
  /* ------------------------------------------------------------------ */

  private URI url(AiProviderConfig provider, String path) {
    String base = provider.getBaseUrl();
    if (base == null || base.isBlank()) {
      throw new RuntimeException("Provider sans base-url : " + provider.getType());
    }
    String b = base.endsWith("/") ? base.substring(0, base.length() - 1) : base;
    return URI.create(b + path);
  }

  private Map<String, Object> result(JsonNode contentNode, JsonNode root) {
    String content =
        contentNode.isMissingNode() || contentNode.isNull() ? "" : contentNode.asText();
    JsonNode usage = root.path("usage");
    int tokens = usage.isMissingNode() ? 0 : usage.path("total_tokens").asInt(0);

    Map<String, Object> result = new LinkedHashMap<String, Object>();
    result.put("content", content.trim());
    result.put("tokens", tokens);
    result.put("raw", root.toString());
    return result;
  }
}
