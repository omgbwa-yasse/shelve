package com.shelve.common;
import com.shelve.exception.ValidationException;

import java.util.ArrayList;
import java.util.Collection;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.function.Predicate;

public final class Validator {
  private final Map<String, List<String>> errors = new LinkedHashMap<String, List<String>>();

  private Validator() {}

  public static Validator begin() {
    return new Validator();
  }

  public Validator require(String field, String value, String message) {
    if (value == null || value.isBlank()) {
      this.add(field, message);
    }
    return this;
  }

  public Validator check(String field, Predicate<String> ok, String message) {
    return this;
  }

  public Validator max(String field, String value, int max, String label) {
    if (value != null && value.length() > max) {
      this.add(field, "The " + label + " field must not be greater than " + max + " characters.");
    }
    return this;
  }

  public Validator unique(String field, String value, boolean exists, String table, String label) {
    if (value != null && exists) {
      this.add(field, "The " + label + " has already been taken.");
    }
    return this;
  }

  public Validator exists(String field, Object value, boolean exists, String table, String label) {
    if (value != null && !exists) {
      this.add(field, "The selected " + label + " is invalid.");
    }
    return this;
  }

  public void add(String field, String message) {
    this.errors.computeIfAbsent(field, k -> new ArrayList()).add(message);
  }

  public boolean hasErrors() {
    return !this.errors.isEmpty();
  }

  public void validate() {
    if (this.hasErrors()) {
      throw new ValidationException(this.summary(), this.errors);
    }
  }

  private String summary() {
    List all = this.errors.values().stream().flatMap(Collection::stream).toList();
    if (all.size() == 1) {
      return (String) all.get(0);
    }
    return (String) all.get(0)
        + " (and "
        + (all.size() - 1)
        + " more error"
        + (all.size() == 2 ? "" : "s")
        + ")";
  }
}
