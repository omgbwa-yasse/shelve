package com.shelve.slips.dto;

import java.time.Instant;

public record SlipStatusView(
    Long id,
    String name,
    String description,
    long slipsCount,
    Instant createdAt,
    Instant updatedAt) {}
