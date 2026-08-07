package com.shelve.organisation.dto;

import java.time.Instant;

public record OrganisationView(
    Long id,
    String code,
    String name,
    String description,
    Long parentId,
    Instant createdAt,
    Instant updatedAt) {}
