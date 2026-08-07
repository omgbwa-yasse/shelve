package com.shelve.common;

import java.util.List;
import java.util.Map;
import org.springframework.http.HttpStatus;

public class ApiError {
  private String type;
  private String title;
  private int status;
  private String detail;
  private String instance;
  private Map<String, List<String>> errors;

  public static ApiError of(HttpStatus status, String title) {
    ApiError e = new ApiError();
    e.type = "about:blank";
    e.status = status.value();
    e.title = title;
    return e;
  }

  public static ApiError validation(Map<String, List<String>> errors) {
    ApiError e = new ApiError();
    e.type = "https://shelve.local/errors/validation";
    e.status = 422;
    e.title = "Les donn\u00e9es envoy\u00e9es sont invalides.";
    e.detail = errors.size() + " champ(s) en erreur.";
    e.errors = errors;
    return e;
  }

  public String getType() {
    return this.type;
  }

  public void setType(String type) {
    this.type = type;
  }

  public String getTitle() {
    return this.title;
  }

  public void setTitle(String title) {
    this.title = title;
  }

  public int getStatus() {
    return this.status;
  }

  public void setStatus(int status) {
    this.status = status;
  }

  public String getDetail() {
    return this.detail;
  }

  public void setDetail(String detail) {
    this.detail = detail;
  }

  public String getInstance() {
    return this.instance;
  }

  public void setInstance(String instance) {
    this.instance = instance;
  }

  public Map<String, List<String>> getErrors() {
    return this.errors;
  }

  public void setErrors(Map<String, List<String>> errors) {
    this.errors = errors;
  }
}
