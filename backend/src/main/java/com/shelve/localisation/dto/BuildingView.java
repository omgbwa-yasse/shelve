package com.shelve.localisation.dto;

import java.time.Instant;

public record BuildingView(
    Long id,
    String name,
    String description,
    String visibility,
    boolean isPublic,
    boolean isPrivate,
    boolean inheritsVisibility,
    Long creatorId,
    Instant createdAt,
    Instant updatedAt) {}
