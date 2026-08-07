package com.shelve.localisation.dto;

import java.time.Instant;

public record ContainerPropertyView(
    Long id,
    String name,
    Double width,
    Double length,
    Double depth,
    Long creatorId,
    Instant createdAt,
    Instant updatedAt) {}
