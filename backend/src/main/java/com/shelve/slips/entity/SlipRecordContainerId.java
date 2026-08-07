package com.shelve.slips.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Embeddable;
import java.io.Serializable;
import java.util.Objects;

@Embeddable
public class SlipRecordContainerId implements Serializable {
  @Column(name = "slip_record_id", nullable = false)
  private Long slipRecordId;

  @Column(name = "container_id", nullable = false)
  private Long containerId;

  public SlipRecordContainerId() {}

  public SlipRecordContainerId(Long slipRecordId, Long containerId) {
    this.slipRecordId = slipRecordId;
    this.containerId = containerId;
  }

  public Long getSlipRecordId() {
    return this.slipRecordId;
  }

  public void setSlipRecordId(Long v) {
    this.slipRecordId = v;
  }

  public Long getContainerId() {
    return this.containerId;
  }

  public void setContainerId(Long v) {
    this.containerId = v;
  }

  public boolean equals(Object o) {
    if (this == o) {
      return true;
    }
    if (!(o instanceof SlipRecordContainerId)) {
      return false;
    }
    SlipRecordContainerId that = (SlipRecordContainerId) o;
    return Objects.equals(this.slipRecordId, that.slipRecordId)
        && Objects.equals(this.containerId, that.containerId);
  }

  public int hashCode() {
    return Objects.hash(this.slipRecordId, this.containerId);
  }
}
