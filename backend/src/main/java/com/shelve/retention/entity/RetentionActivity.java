package com.shelve.retention.entity;

import jakarta.persistence.Column;
import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "retention_activity")
public class RetentionActivity {
  @EmbeddedId private RetentionActivityId id;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public RetentionActivityId getId() {
    return this.id;
  }

  public void setId(RetentionActivityId id) {
    this.id = id;
  }

  public Long getRetentionId() {
    return this.id != null ? this.id.getRetentionId() : null;
  }

  public Long getActivityId() {
    return this.id != null ? this.id.getActivityId() : null;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
