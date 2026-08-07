package com.shelve.organisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Embeddable;
import java.io.Serializable;
import java.util.Objects;

@Embeddable
public class UserOrganisationRoleId implements Serializable {
  @Column(name = "user_id", nullable = false)
  private Long userId;

  @Column(name = "organisation_id", nullable = false)
  private Long organisationId;

  public UserOrganisationRoleId() {}

  public UserOrganisationRoleId(Long userId, Long organisationId) {
    this.userId = userId;
    this.organisationId = organisationId;
  }

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long userId) {
    this.userId = userId;
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
    if (!(o instanceof UserOrganisationRoleId)) {
      return false;
    }
    UserOrganisationRoleId that = (UserOrganisationRoleId) o;
    return Objects.equals(this.userId, that.userId)
        && Objects.equals(this.organisationId, that.organisationId);
  }

  public int hashCode() {
    return Objects.hash(this.userId, this.organisationId);
  }
}
