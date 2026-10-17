package com.shelve.retention.entity;

import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;

@Entity
@Table(name = "retention_activity")
public class RetentionActivity {
  @EmbeddedId private RetentionActivityId id;

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
}
