package com.shelve.common;

import java.time.Instant;
import java.time.ZoneOffset;
import java.time.format.DateTimeFormatter;
import java.util.LinkedHashMap;
import java.util.Map;

public final class Json {
  private static final DateTimeFormatter ISO =
      DateTimeFormatter.ofPattern("yyyy-MM-dd'T'HH:mm:ss'Z'");

  private Json() {}

  public static String timestamp(Instant instant) {
    return instant != null ? ISO.format(instant.atZone(ZoneOffset.UTC)) : null;
  }

  public static Map<String, Object> of(String key, Object value) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put(key, value);
    return map;
  }
}
