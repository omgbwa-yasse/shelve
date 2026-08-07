package com.shelve.referentials.dto;

import java.time.Instant;

public record ActivityView(
    Long id,
    String code,
    String name,
    String observation,
    Long parentId,
    Long communicabilityId,
    Instant createdAt,
    Instant updatedAt) {}
