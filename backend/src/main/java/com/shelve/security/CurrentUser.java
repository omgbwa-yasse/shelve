package com.shelve.security;

import com.shelve.exception.ApiException;
import org.springframework.http.HttpStatus;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;

public final class CurrentUser {
  private CurrentUser() {}

  public static AuthenticatedUser get() {
    Object object;
    Authentication auth = SecurityContextHolder.getContext().getAuthentication();
    if (auth != null && (object = auth.getPrincipal()) instanceof AuthenticatedUser) {
      AuthenticatedUser au = (AuthenticatedUser) object;
      return au;
    }
    throw new ApiException(HttpStatus.UNAUTHORIZED, "Unauthenticated.");
  }

  public static AuthenticatedUser orNull() {
    Object object;
    Authentication auth = SecurityContextHolder.getContext().getAuthentication();
    if (auth != null && (object = auth.getPrincipal()) instanceof AuthenticatedUser) {
      AuthenticatedUser au = (AuthenticatedUser) object;
      return au;
    }
    return null;
  }
}
