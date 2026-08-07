package com.shelve.localisation.dto;

import java.time.Instant;

public record ContainerView(
    Long id,
    String code,
    Long shelveId,
    Long statusId,
    Long propertyId,
    Long creatorId,
    Long creatorOrganisationId,
    boolean isArchived,
    Instant createdAt,
    Instant updatedAt) {}
