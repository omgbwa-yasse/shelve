package com.shelve.ai.sandbox.dto;

import java.time.Instant;
import java.time.LocalDateTime;

public record SandboxView(
    Long id,
    Long organisationId,
    Long userId,
    Long conversationId,
    String name,
    String pattern,
    String engine,
    String status,
    String folder,
    String lastOutput,
    LocalDateTime expiresAt,
    Instant createdAt,
    Instant updatedAt) {}
