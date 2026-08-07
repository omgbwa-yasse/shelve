package com.shelve.organisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "user_organisation_role")
public class UserOrganisationRole {
  @EmbeddedId private UserOrganisationRoleId id;

  @Column(name = "role_id", nullable = false)
  private Long roleId;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public UserOrganisationRoleId getId() {
    return this.id;
  }

  public void setId(UserOrganisationRoleId id) {
    this.id = id;
  }

  public Long getUserId() {
    return this.id != null ? this.id.getUserId() : null;
  }

  public Long getOrganisationId() {
    return this.id != null ? this.id.getOrganisationId() : null;
  }

  public Long getRoleId() {
    return this.roleId;
  }

  public void setRoleId(Long roleId) {
    this.roleId = roleId;
  }

  public Long getCreatorId() {
    return this.creatorId;
  }

  public void setCreatorId(Long creatorId) {
    this.creatorId = creatorId;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
