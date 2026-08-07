package com.shelve.organisation.dto;

import java.time.Instant;

public record UserOrganisationRoleView(
    Long userId,
    Long organisationId,
    Long roleId,
    Long creatorId,
    Instant createdAt,
    Instant updatedAt) {}
