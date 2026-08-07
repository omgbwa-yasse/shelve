package com.shelve.security;

import com.shelve.exception.ApiException;
import org.springframework.http.HttpStatus;

public final class Policy {
  private Policy() {}

  public static void check(AuthenticatedUser user, String permission) {
    if (user.isSuperAdmin()) {
      return;
    }
    if (!user.hasPermission(permission)) {
      throw new ApiException(HttpStatus.FORBIDDEN, "Cette action n'est pas autoris\u00e9e.");
    }
  }

  public static void checkOrNotFound(AuthenticatedUser user, String permission) {
    if (user.isSuperAdmin()) {
      return;
    }
    if (!user.hasPermission(permission)) {
      throw ApiException.notFound();
    }
  }
}
