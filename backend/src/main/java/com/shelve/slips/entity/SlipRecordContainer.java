package com.shelve.slips.entity;

import jakarta.persistence.Column;
import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "slip_record_container")
public class SlipRecordContainer {
  @EmbeddedId private SlipRecordContainerId id;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @Column(nullable = false, length = 200)
  private String description;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public SlipRecordContainerId getId() {
    return this.id;
  }

  public void setId(SlipRecordContainerId id) {
    this.id = id;
  }

  public Long getCreatorId() {
    return this.creatorId;
  }

  public void setCreatorId(Long v) {
    this.creatorId = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
