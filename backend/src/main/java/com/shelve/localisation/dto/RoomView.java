package com.shelve.localisation.dto;

import java.time.Instant;

public record RoomView(
    Long id,
    String code,
    String name,
    String description,
    String visibility,
    String effectiveVisibility,
    boolean isVisible,
    String type,
    Long floorId,
    long shelvesCount,
    Long creatorId,
    Instant createdAt,
    Instant updatedAt) {}
