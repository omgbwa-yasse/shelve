package com.shelve.organisation.mapper;

import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.entity.User;
import java.time.Instant;
import java.time.ZoneOffset;
import java.time.format.DateTimeFormatter;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

public final class UserViewMapper {
  private static final DateTimeFormatter ISO =
      DateTimeFormatter.ofPattern("yyyy-MM-dd'T'HH:mm:ss'Z'");

  private UserViewMapper() {}

  public static Map<String, Object> toMap(
      User user,
      boolean isSuperadmin,
      Organisation currentOrganisation,
      List<Organisation> organisations,
      List<String> roles,
      boolean withContext) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", user.getId());
    map.put("name", user.getName());
    map.put("surname", user.getSurname());
    map.put("email", user.getEmail());
    map.put("birthday", user.getBirthday() != null ? user.getBirthday().toString() : null);
    map.put("current_organisation_id", user.getCurrentOrganisationId());
    map.put("is_superadmin", isSuperadmin);
    map.put("created_at", UserViewMapper.format(user.getCreatedAt()));
    map.put("updated_at", UserViewMapper.format(user.getUpdatedAt()));
    if (withContext) {
      map.put("current_organisation", UserViewMapper.brief(currentOrganisation));
      map.put(
          "organisations",
          organisations == null
              ? List.of()
              : organisations.stream().map(UserViewMapper::brief).toList());
      map.put("roles", roles == null ? List.of() : roles);
    }
    return map;
  }

  private static Map<String, Object> brief(Organisation org) {
    if (org == null) {
      return null;
    }
    LinkedHashMap<String, Object> m = new LinkedHashMap<String, Object>();
    m.put("id", org.getId());
    m.put("name", org.getName());
    m.put("code", org.getCode());
    return m;
  }

  private static String format(Instant instant) {
    return instant != null ? ISO.format(instant.atZone(ZoneOffset.UTC)) : null;
  }
}
