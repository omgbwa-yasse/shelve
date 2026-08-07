package com.shelve.localisation.dto;

import java.time.Instant;

public record ContainerStatusView(
    Long id,
    String name,
    String description,
    Long creatorId,
    Instant createdAt,
    Instant updatedAt) {}
