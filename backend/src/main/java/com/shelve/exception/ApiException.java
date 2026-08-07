package com.shelve.exception;

import org.springframework.http.HttpStatus;

public class ApiException extends RuntimeException {
  private final HttpStatus status;
  private final String detail;

  public ApiException(HttpStatus status, String message) {
    this(status, message, null);
  }

  public ApiException(HttpStatus status, String message, String detail) {
    super(message);
    this.status = status;
    this.detail = detail;
  }

  public HttpStatus getStatus() {
    return this.status;
  }

  public String getDetail() {
    return this.detail;
  }

  public static ApiException badRequest(String message) {
    return new ApiException(HttpStatus.BAD_REQUEST, message);
  }

  public static ApiException notFound() {
    return new ApiException(HttpStatus.NOT_FOUND, "Ressource introuvable.");
  }

  public static ApiException notFound(String message) {
    return new ApiException(HttpStatus.NOT_FOUND, message);
  }

  public static ApiException conflict(String message) {
    return new ApiException(HttpStatus.CONFLICT, message);
  }
}
