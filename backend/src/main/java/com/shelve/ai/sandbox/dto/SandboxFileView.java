package com.shelve.ai.sandbox.dto;

import java.time.Instant;

public record SandboxFileView(
    Long id,
    Long sandboxId,
    String section,
    String path,
    String name,
    Long size,
    String mime,
    String hash,
    Instant createdAt) {}
