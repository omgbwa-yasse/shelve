package com.shelve.referentials.dto;

import java.time.Instant;

public record ReferenceValueView(
    Long id,
    Long listId,
    String value,
    String code,
    String description,
    Boolean active,
    Integer sortOrder,
    Instant createdAt,
    Instant updatedAt) {}
