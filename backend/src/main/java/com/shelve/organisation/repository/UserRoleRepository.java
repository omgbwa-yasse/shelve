package com.shelve.organisation.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.entity.Role;
import com.shelve.organisation.entity.UserRole;

public interface UserRoleRepository extends JpaRepository<UserRole, Long> {
  @Query(
      value =
          "select r.name from Role r join UserRole ur on ur.roleId = r.id where ur.userId ="
              + " :userId")
  public List<String> findRoleNamesByUserId(@Param(value = "userId") Long var1);
}
