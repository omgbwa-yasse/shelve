package com.shelve.organisation.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.entity.Permission;
import com.shelve.organisation.entity.RolePermission;
import com.shelve.organisation.entity.UserPermission;
import com.shelve.organisation.entity.UserRole;

public interface PermissionRepository extends JpaRepository<Permission, Long> {
  @Query(
      value =
          "select p.name from Permission p join UserPermission up on up.permissionId = p.id where"
              + " up.userId = :userId")
  public List<String> findNamesForUserDirect(@Param(value = "userId") Long var1);

  @Query(
      value =
          "select distinct p.name from Permission p\n"
              + "join RolePermission rp on rp.id.permissionId = p.id\n"
              + "join UserRole ur on ur.roleId = rp.id.roleId\n"
              + "where ur.userId = :userId\n")
  public List<String> findNamesForUserViaRoles(@Param(value = "userId") Long var1);

  @Query(
      value =
          "select distinct p.name from Permission p\n"
              + "join RolePermission rp on rp.id.permissionId = p.id\n"
              + "join UserOrganisationRole uor on uor.roleId = rp.id.roleId\n"
              + "where uor.id.userId = :userId and uor.id.organisationId = :organisationId\n")
  public List<String> findNamesForUserViaOrgRole(
      @Param(value = "userId") Long userId,
      @Param(value = "organisationId") Long organisationId);
}
