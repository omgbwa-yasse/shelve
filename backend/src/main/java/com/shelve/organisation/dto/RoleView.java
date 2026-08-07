package com.shelve.organisation.dto;

import java.time.Instant;

public record RoleView(
    Long id,
    String name,
    String guardName,
    String description,
    Instant createdAt,
    Instant updatedAt) {}
