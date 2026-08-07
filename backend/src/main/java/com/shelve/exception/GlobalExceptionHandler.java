package com.shelve.exception;
import com.shelve.common.ApiError;

import jakarta.servlet.http.HttpServletRequest;
import java.util.ArrayList;
import java.util.Collection;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.http.HttpStatusCode;
import org.springframework.http.ResponseEntity;
import org.springframework.http.converter.HttpMessageNotReadableException;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.MissingServletRequestParameterException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import org.springframework.web.method.annotation.MethodArgumentTypeMismatchException;
import org.springframework.web.servlet.resource.NoResourceFoundException;

@RestControllerAdvice
public class GlobalExceptionHandler {
  private static final Logger log = LoggerFactory.getLogger(GlobalExceptionHandler.class);

  @ExceptionHandler(value = {ValidationException.class})
  public ResponseEntity<Object> handleValidation(
      ValidationException ex, HttpServletRequest request) {
    LinkedHashMap<String, Object> body = new LinkedHashMap<String, Object>();
    body.put("message", ex.getMessage());
    body.put("errors", ex.getErrors());
    return ResponseEntity.status((HttpStatusCode) HttpStatus.UNPROCESSABLE_ENTITY).body(body);
  }

  @ExceptionHandler(value = {ApiException.class})
  public ResponseEntity<ApiError> handleApi(ApiException ex, HttpServletRequest request) {
    ApiError error = ApiError.of(ex.getStatus(), ex.getMessage());
    error.setDetail(ex.getDetail());
    error.setInstance(request.getRequestURI());
    return ResponseEntity.status(ex.getStatus()).body(error);
  }

  @ExceptionHandler(value = {MethodArgumentNotValidException.class})
  public ResponseEntity<Object> handleBeanValidation(MethodArgumentNotValidException ex) {
    LinkedHashMap<String, List<String>> errors = new LinkedHashMap<>();
    for (FieldError fe : ex.getBindingResult().getFieldErrors()) {
      errors.computeIfAbsent(fe.getField(), k -> new ArrayList<>()).add(fe.getDefaultMessage());
    }
    String message =
        errors.values().stream()
            .flatMap(Collection::stream)
            .findFirst()
            .orElse("Les donn\u00e9es envoy\u00e9es sont invalides.");
    LinkedHashMap<String, Object> body = new LinkedHashMap<String, Object>();
    body.put("message", message);
    body.put("errors", errors);
    return ResponseEntity.status((HttpStatusCode) HttpStatus.UNPROCESSABLE_ENTITY).body(body);
  }

  @ExceptionHandler(value = {MethodArgumentTypeMismatchException.class})
  public ResponseEntity<Object> handleTypeMismatch(MethodArgumentTypeMismatchException ex) {
    return ResponseEntity.status((HttpStatusCode) HttpStatus.BAD_REQUEST)
        .body(Map.of("message", "Le param\u00e8tre n'est pas valide."));
  }

  @ExceptionHandler(value = {MissingServletRequestParameterException.class})
  public ResponseEntity<Object> handleMissingParam(MissingServletRequestParameterException ex) {
    return ResponseEntity.status((HttpStatusCode) HttpStatus.BAD_REQUEST)
        .body(Map.of("message", "Le param\u00e8tre " + ex.getParameterName() + " est requis."));
  }

  @ExceptionHandler(value = {HttpMessageNotReadableException.class})
  public ResponseEntity<Object> handleUnreadable(HttpMessageNotReadableException ex) {
    return ResponseEntity.status((HttpStatusCode) HttpStatus.BAD_REQUEST)
        .body(Map.of("message", "Le corps de la requ\u00eate est invalide."));
  }

  @ExceptionHandler(value = {NoResourceFoundException.class})
  public ResponseEntity<Object> handleNoResource(NoResourceFoundException ex) {
    return ResponseEntity.status((HttpStatusCode) HttpStatus.NOT_FOUND)
        .body(Map.of("message", "Ressource introuvable."));
  }

  @ExceptionHandler(value = {Exception.class})
  public ResponseEntity<Object> handleGeneric(Exception ex, HttpServletRequest request) {
    log.error(
        "Erreur non g\u00e9r\u00e9e sur {} : {}",
        new Object[] {request.getRequestURI(), ex.getMessage(), ex});
    return ResponseEntity.status((HttpStatusCode) HttpStatus.INTERNAL_SERVER_ERROR)
        .body(Map.of("message", "Une erreur interne est survenue."));
  }
}
