package com.shelve.organisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Embeddable;
import java.io.Serializable;
import java.util.Objects;

@Embeddable
public class RolePermissionId implements Serializable {
  @Column(name = "role_id", nullable = false)
  private Long roleId;

  @Column(name = "permission_id", nullable = false)
  private Long permissionId;

  public RolePermissionId() {}

  public RolePermissionId(Long roleId, Long permissionId) {
    this.roleId = roleId;
    this.permissionId = permissionId;
  }

  public Long getRoleId() {
    return this.roleId;
  }

  public void setRoleId(Long roleId) {
    this.roleId = roleId;
  }

  public Long getPermissionId() {
    return this.permissionId;
  }

  public void setPermissionId(Long permissionId) {
    this.permissionId = permissionId;
  }

  public boolean equals(Object o) {
    if (this == o) {
      return true;
    }
    if (!(o instanceof RolePermissionId)) {
      return false;
    }
    RolePermissionId that = (RolePermissionId) o;
    return Objects.equals(this.roleId, that.roleId)
        && Objects.equals(this.permissionId, that.permissionId);
  }

  public int hashCode() {
    return Objects.hash(this.roleId, this.permissionId);
  }
}
