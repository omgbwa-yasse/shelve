package com.shelve.security;

import com.shelve.organisation.entity.User;
import java.util.List;

public record AuthenticatedUser(
    User user, List<String> permissions, long tokenId, boolean isSuperadmin) {
  public boolean hasPermission(String permission) {
    return this.permissions.contains(permission);
  }

  public boolean isSuperAdmin() {
    return this.isSuperadmin;
  }
}
