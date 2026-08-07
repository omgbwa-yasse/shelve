package com.shelve.organisation.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.entity.UserOrganisationRole;
import com.shelve.organisation.entity.UserOrganisationRoleId;

public interface UserOrganisationRoleRepository
    extends JpaRepository<UserOrganisationRole, UserOrganisationRoleId>,
        JpaSpecificationExecutor<UserOrganisationRole> {
  @Query(value = "select uor.roleId from UserOrganisationRole uor where uor.id.userId = :userId")
  public List<Long> findRoleIdsByUserId(@Param(value = "userId") Long var1);

  @Query(
      value =
          "select uor.id.organisationId from UserOrganisationRole uor where uor.id.userId ="
              + " :userId")
  public List<Long> findOrganisationIdsByUserId(@Param(value = "userId") Long var1);

  public boolean existsById(UserOrganisationRoleId var1);
}
