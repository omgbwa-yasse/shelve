package com.shelve.localisation.dto;

import java.time.Instant;

public record FloorView(
    Long id,
    String name,
    String description,
    Long buildingId,
    Long creatorId,
    Instant createdAt,
    Instant updatedAt) {}
