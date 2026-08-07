package com.shelve.localisation.dto;

import java.time.Instant;

public record ShelfView(
    Long id,
    String code,
    String observation,
    Double face,
    Double ear,
    Double shelf,
    Double shelfLength,
    Long roomId,
    Long creatorId,
    double totalCapacity,
    long occupiedSpots,
    long availableSpots,
    double occupancyPercentage,
    double volumetryMl,
    Instant createdAt,
    Instant updatedAt) {}
