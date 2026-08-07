package com.shelve.localisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Embeddable;
import java.io.Serializable;
import java.util.Objects;

@Embeddable
public class OrganisationRoomId implements Serializable {
  @Column(name = "room_id", nullable = false)
  private Long roomId;

  @Column(name = "organisation_id", nullable = false)
  private Long organisationId;

  public OrganisationRoomId() {}

  public OrganisationRoomId(Long roomId, Long organisationId) {
    this.roomId = roomId;
    this.organisationId = organisationId;
  }

  public Long getRoomId() {
    return this.roomId;
  }

  public void setRoomId(Long roomId) {
    this.roomId = roomId;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long organisationId) {
    this.organisationId = organisationId;
  }

  public boolean equals(Object o) {
    if (this == o) {
      return true;
    }
    if (!(o instanceof OrganisationRoomId)) {
      return false;
    }
    OrganisationRoomId that = (OrganisationRoomId) o;
    return Objects.equals(this.roomId, that.roomId)
        && Objects.equals(this.organisationId, that.organisationId);
  }

  public int hashCode() {
    return Objects.hash(this.roomId, this.organisationId);
  }
}
