package com.shelve.slips.dto;

import java.time.Instant;

public record SlipView(
    Long id,
    String code,
    String name,
    String description,
    Long officerOrganisationId,
    Long officerId,
    Long userOrganisationId,
    Long userId,
    Long slipStatusId,
    boolean isReceived,
    Instant receivedDate,
    Long receivedBy,
    boolean isApproved,
    Instant approvedDate,
    Long approvedBy,
    boolean isIntegrated,
    Instant integratedDate,
    Long integratedBy,
    long recordsCount,
    Instant createdAt,
    Instant updatedAt) {}
