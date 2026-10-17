package com.shelve.opac.service;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import java.util.LinkedHashMap;
import java.util.Map;
import org.springframework.stereotype.Component;

/** Sérialisation JSON des colonnes `settings` / `responsive` / `meta`. */
@Component
public class OpacJson {
  private final ObjectMapper mapper;

  public OpacJson(ObjectMapper mapper) {
    this.mapper = mapper;
  }

  public String write(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return this.mapper.writeValueAsString(value);
    } catch (Exception e) {
      return null;
    }
  }

  public Map<String, Object> readMap(String json) {
    if (json == null || json.isBlank()) {
      return new LinkedHashMap<String, Object>();
    }
    try {
      return this.mapper.readValue(json, new TypeReference<Map<String, Object>>() {});
    } catch (Exception e) {
      return new LinkedHashMap<String, Object>();
    }
  }
}
