package com.shelve.retention.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Embeddable;
import java.io.Serializable;
import java.util.Objects;

@Embeddable
public class RetentionActivityId implements Serializable {
  @Column(name = "retention_id", nullable = false)
  private Long retentionId;

  @Column(name = "activity_id", nullable = false)
  private Long activityId;

  public RetentionActivityId() {}

  public RetentionActivityId(Long retentionId, Long activityId) {
    this.retentionId = retentionId;
    this.activityId = activityId;
  }

  public Long getRetentionId() {
    return this.retentionId;
  }

  public void setRetentionId(Long v) {
    this.retentionId = v;
  }

  public Long getActivityId() {
    return this.activityId;
  }

  public void setActivityId(Long v) {
    this.activityId = v;
  }

  public boolean equals(Object o) {
    if (this == o) {
      return true;
    }
    if (!(o instanceof RetentionActivityId)) {
      return false;
    }
    RetentionActivityId that = (RetentionActivityId) o;
    return Objects.equals(this.retentionId, that.retentionId)
        && Objects.equals(this.activityId, that.activityId);
  }

  public int hashCode() {
    return Objects.hash(this.retentionId, this.activityId);
  }
}
