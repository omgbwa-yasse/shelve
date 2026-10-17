package com.shelve.seed;

import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.entity.Role;
import com.shelve.organisation.entity.User;
import com.shelve.organisation.entity.UserOrganisationRole;
import com.shelve.organisation.entity.UserOrganisationRoleId;
import com.shelve.organisation.entity.UserRole;
import com.shelve.organisation.repository.OrganisationRepository;
import com.shelve.organisation.repository.RoleRepository;
import com.shelve.organisation.repository.UserOrganisationRoleRepository;
import com.shelve.organisation.repository.UserRepository;
import com.shelve.organisation.repository.UserRoleRepository;
import java.time.LocalDate;
import java.util.Optional;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Comptes d'accès — transposition de `Settings/AdminAccountSeeder.php`.
 * Garantit le rôle `superadmin` et un compte administrateur fonctionnel,
 * rattaché à la première organisation. Idempotent.
 */
@Component
public class AdminSeeder {
  private static final Logger log = LoggerFactory.getLogger(AdminSeeder.class);

  public static final String ADMIN_EMAIL = "admin@example.com";
  public static final String ADMIN_PASSWORD = "admin123";

  private final UserRepository userRepository;
  private final RoleRepository roleRepository;
  private final UserRoleRepository userRoleRepository;
  private final UserOrganisationRoleRepository userOrganisationRoleRepository;
  private final OrganisationRepository organisationRepository;
  private final PasswordEncoder passwordEncoder;

  public AdminSeeder(
      UserRepository userRepository,
      RoleRepository roleRepository,
      UserRoleRepository userRoleRepository,
      UserOrganisationRoleRepository userOrganisationRoleRepository,
      OrganisationRepository organisationRepository,
      PasswordEncoder passwordEncoder) {
    this.userRepository = userRepository;
    this.roleRepository = roleRepository;
    this.userRoleRepository = userRoleRepository;
    this.userOrganisationRoleRepository = userOrganisationRoleRepository;
    this.organisationRepository = organisationRepository;
    this.passwordEncoder = passwordEncoder;
  }

  @Transactional
  public void run() {
    Role superadmin =
        this.roleRepository
            .findAll((root, q, cb) -> cb.equal(root.get("name"), "superadmin"))
            .stream()
            .findFirst()
            .orElseGet(() -> {
              Role r = new Role();
              r.setName("superadmin");
              r.setGuardName("web");
              r.setDescription("Super administrateur avec tous les droits");
              return this.roleRepository.save(r);
            });

    Optional<Organisation> org = this.organisationRepository.findAll().stream().findFirst();
    if (org.isEmpty()) {
      log.warn("AdminSeeder : aucune organisation — compte admin non rattaché.");
      return;
    }

    User admin =
        this.userRepository.findByEmail(ADMIN_EMAIL).orElseGet(() -> {
          User u = new User();
          u.setEmail(ADMIN_EMAIL);
          u.setName("Admin");
          u.setSurname("Principal");
          u.setBirthday(LocalDate.of(1990, 1, 1));
          u.setPassword(this.passwordEncoder.encode(ADMIN_PASSWORD));
          u.setCurrentOrganisationId(org.get().getId());
          return this.userRepository.save(u);
        });

    // Rôle global superadmin (user_roles) — c'est ce que lit `isSuperAdmin()`.
    boolean hasGlobalRole =
        this.userRoleRepository.findRoleNamesByUserId(admin.getId()).contains("superadmin");
    if (!hasGlobalRole) {
      UserRole ur = new UserRole();
      ur.setUserId(admin.getId());
      ur.setRoleId(superadmin.getId());
      this.userRoleRepository.save(ur);
    }

    // Rattachement organisation (user_organisation_role) — role_id + creator_id NOT NULL.
    if (!this.userOrganisationRoleRepository.existsById(
        new UserOrganisationRoleId(admin.getId(), org.get().getId()))) {
      UserOrganisationRole uor = new UserOrganisationRole();
      uor.setId(new UserOrganisationRoleId(admin.getId(), org.get().getId()));
      uor.setRoleId(superadmin.getId());
      uor.setCreatorId(admin.getId());
      this.userOrganisationRoleRepository.save(uor);
    }

    log.info(
        "Compte administrateur prêt : {} / {} (rôle superadmin, org {})",
        ADMIN_EMAIL, ADMIN_PASSWORD, org.get().getCode());
  }
}
