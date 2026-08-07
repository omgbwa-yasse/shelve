package com.shelve.referentials.dto;

import java.time.Instant;

public record KeywordView(
    Long id, String name, String description, Instant createdAt, Instant updatedAt) {}
