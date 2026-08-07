package com.shelve.referentials.dto;

import java.time.Instant;
import java.util.List;

public record ReferenceListView(
    Long id,
    String name,
    String code,
    String description,
    Boolean active,
    Long createdBy,
    Long updatedBy,
    long valuesCount,
    List<ReferenceValueView> values,
    boolean withValues,
    Instant createdAt,
    Instant updatedAt) {}
