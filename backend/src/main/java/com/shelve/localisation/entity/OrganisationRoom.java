package com.shelve.localisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import java.time.Instant;

@Entity
@Table(name = "organisation_room")
public class OrganisationRoom {
  @EmbeddedId private OrganisationRoomId id;

  @Column(name = "created_at", insertable = false, updatable = false)
  private Instant createdAt;

  @Column(name = "updated_at", insertable = false, updatable = false)
  private Instant updatedAt;

  public OrganisationRoomId getId() {
    return this.id;
  }

  public void setId(OrganisationRoomId id) {
    this.id = id;
  }

  public Long getRoomId() {
    return this.id != null ? this.id.getRoomId() : null;
  }

  public Long getOrganisationId() {
    return this.id != null ? this.id.getOrganisationId() : null;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
