package com.shelve.organisation.dto;

import java.time.Instant;
import java.time.LocalDate;
import java.util.List;

public record UserView(
    Long id,
    String name,
    String surname,
    String email,
    LocalDate birthday,
    Long currentOrganisationId,
    Boolean isSuperadmin,
    Instant createdAt,
    Instant updatedAt,
    OrganisationBrief currentOrganisation,
    List<OrganisationBrief> organisations,
    List<String> roles,
    boolean withContext) {
  public record OrganisationBrief(Long id, String name, String code) {}
}
