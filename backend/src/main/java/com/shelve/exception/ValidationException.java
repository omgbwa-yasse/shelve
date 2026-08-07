package com.shelve.exception;

import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

public class ValidationException extends RuntimeException {
  private final Map<String, List<String>> errors;

  public ValidationException(Map<String, List<String>> errors) {
    super(ValidationException.firstMessage(errors));
    this.errors = errors;
  }

  public ValidationException(String message, Map<String, List<String>> errors) {
    super(message);
    this.errors = errors;
  }

  public Map<String, List<String>> getErrors() {
    return this.errors;
  }

  private static String firstMessage(Map<String, List<String>> errors) {
    for (List<String> messages : errors.values()) {
      if (messages.isEmpty()) continue;
      return messages.get(0);
    }
    return "Les donn\u00e9es envoy\u00e9es sont invalides.";
  }

  public static ValidationException single(String field, String message) {
    LinkedHashMap<String, List<String>> errors = new LinkedHashMap<String, List<String>>();
    errors.put(field, List.of(message));
    return new ValidationException(message, errors);
  }

  public static ValidationException of(Map<String, List<String>> errors) {
    return new ValidationException(errors);
  }
}
