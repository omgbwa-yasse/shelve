package com.shelve.organisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import java.time.Instant;

@Entity
@Table(name = "role_permissions")
public class RolePermission {
  @EmbeddedId private RolePermissionId id;

  @Column(name = "created_at", insertable = false, updatable = false)
  private Instant createdAt;

  @Column(name = "updated_at", insertable = false, updatable = false)
  private Instant updatedAt;

  public RolePermissionId getId() {
    return this.id;
  }

  public void setId(RolePermissionId id) {
    this.id = id;
  }

  public Long getRoleId() {
    return this.id != null ? this.id.getRoleId() : null;
  }

  public Long getPermissionId() {
    return this.id != null ? this.id.getPermissionId() : null;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
