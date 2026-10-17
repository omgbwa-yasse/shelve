package com.shelve.settings.service;

import com.shelve.settings.entity.Setting;
import com.shelve.settings.repository.SettingRepository;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.springframework.stereotype.Service;

/**
 * Lecture de la valeur effective d'un paramètre applicatif (`settings`).
 * Priorité : `value` (personnalisée) sinon `default_value` — miroir de
 * `Setting::getEffectiveValue()` côté Laravel. Les valeurs sont stockées en
 * JSON, on les désérialise pour les ramener à leur type réel.
 */
@Service
public class SettingReader {
  private final SettingRepository settingRepository;
  private final ObjectMapper mapper;

  public SettingReader(SettingRepository settingRepository, ObjectMapper mapper) {
    this.settingRepository = settingRepository;
    this.mapper = mapper;
  }

  /** Valeur effective brute (objet désérialisé) ou null si le paramètre n'existe pas. */
  public Object effective(String name) {
    return this.settingRepository
        .findAll((root, q, cb) -> cb.equal(root.get("name"), name))
        .stream()
        .findFirst()
        .map(this::effectiveValue)
        .orElse(null);
  }

  /** Valeur effective comme chaîne (ou fallback). */
  public String stringValue(String name, String fallback) {
    Object value = this.effective(name);
    return value == null ? fallback : String.valueOf(value);
  }

  /** Valeur effective comme booléen (ou fallback). */
  public boolean booleanValue(String name, boolean fallback) {
    Object value = this.effective(name);
    if (value == null) return fallback;
    if (value instanceof Boolean b) return b;
    return Boolean.parseBoolean(String.valueOf(value));
  }

  private Object effectiveValue(Setting s) {
    String raw = s.getValue() != null && !s.getValue().isBlank() ? s.getValue() : s.getDefaultValue();
    if (raw == null || raw.isBlank()) return null;
    try {
      return this.mapper.readValue(raw, Object.class);
    } catch (Exception e) {
      return raw;
    }
  }
}
