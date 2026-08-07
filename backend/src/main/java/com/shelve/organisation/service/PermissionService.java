package com.shelve.organisation.service;

import com.shelve.organisation.repository.PermissionRepository;
import com.shelve.organisation.repository.UserOrganisationRoleRepository;
import com.shelve.organisation.repository.UserRoleRepository;
import java.util.LinkedHashSet;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class PermissionService {
  private final PermissionRepository permissionRepository;
  private final UserRoleRepository userRoleRepository;
  private final UserOrganisationRoleRepository userOrganisationRoleRepository;

  public PermissionService(
      PermissionRepository permissionRepository,
      UserRoleRepository userRoleRepository,
      UserOrganisationRoleRepository userOrganisationRoleRepository) {
    this.permissionRepository = permissionRepository;
    this.userRoleRepository = userRoleRepository;
    this.userOrganisationRoleRepository = userOrganisationRoleRepository;
  }

  @Transactional(readOnly = true)
  public List<String> effectivePermissionNames(Long userId) {
    LinkedHashSet<String> names = new LinkedHashSet<String>();
    names.addAll(this.permissionRepository.findNamesForUserDirect(userId));
    names.addAll(this.permissionRepository.findNamesForUserViaRoles(userId));
    return names.stream().sorted().toList();
  }

  @Transactional(readOnly = true)
  public List<String> effectivePermissionNamesInOrganisation(Long userId, Long organisationId) {
    LinkedHashSet<String> names = new LinkedHashSet<String>();
    names.addAll(this.permissionRepository.findNamesForUserDirect(userId));
    names.addAll(this.permissionRepository.findNamesForUserViaRoles(userId));
    names.addAll(this.permissionRepository.findNamesForUserViaOrgRole(userId, organisationId));
    return names.stream().sorted().toList();
  }

  @Transactional(readOnly = true)
  public boolean hasPermissionInOrganisation(Long userId, Long organisationId, String permission) {
    return this.effectivePermissionNamesInOrganisation(userId, organisationId).contains(permission);
  }

  @Transactional(readOnly = true)
  public boolean isSuperAdmin(Long userId) {
    return this.userRoleRepository.findRoleNamesByUserId(userId).contains("superadmin");
  }

  @Transactional(readOnly = true)
  public List<Long> organisationIdsOf(Long userId) {
    return this.userOrganisationRoleRepository.findOrganisationIdsByUserId(userId);
  }
}
