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
import java.util.List;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Structure organisationnelle de base — transposition de
 * `Workplaces/OrganisationSeeder.php` (socle : pas d'infrastructure physique).
 *
 * Crée la direction générale (DG) et 3 bureaux rattachés (DF, DRH, DADA),
 * puis garantit le compte superadmin rattaché à CHAQUE bureau avec le rôle
 * `superadmin`. Idempotent : ne crée que ce qui manque, ne supprime rien.
 */
@Component
public class OrganisationSeeder {
  private static final Logger log = LoggerFactory.getLogger(OrganisationSeeder.class);

  public static final String SUPERADMIN_EMAIL = "superadmin@example.com";
  public static final String SUPERADMIN_PASSWORD = "superadmin";

  private final OrganisationRepository organisationRepository;
  private final UserRepository userRepository;
  private final RoleRepository roleRepository;
  private final UserRoleRepository userRoleRepository;
  private final UserOrganisationRoleRepository userOrganisationRoleRepository;
  private final PasswordEncoder passwordEncoder;

  public OrganisationSeeder(
      OrganisationRepository organisationRepository,
      UserRepository userRepository,
      RoleRepository roleRepository,
      UserRoleRepository userRoleRepository,
      UserOrganisationRoleRepository userOrganisationRoleRepository,
      PasswordEncoder passwordEncoder) {
    this.organisationRepository = organisationRepository;
    this.userRepository = userRepository;
    this.roleRepository = roleRepository;
    this.userRoleRepository = userRoleRepository;
    this.userOrganisationRoleRepository = userOrganisationRoleRepository;
    this.passwordEncoder = passwordEncoder;
  }

  @Transactional
  public void run() {
    Organisation dg = this.organisation("DG", "Direction Générale", "Direction générale de l'organisation", null);
    Organisation df = this.organisation("DF", "Direction des Finances", "Direction responsable de la gestion financière", dg.getId());
    Organisation drh = this.organisation("DRH", "Direction des Ressources Humaines", "Direction responsable de la gestion des ressources humaines", dg.getId());
    Organisation dada = this.organisation("DADA", "Direction des Archives et Documents Administratifs", "Direction responsable de la gestion des archives et documents administratifs", dg.getId());

    this.ensureSuperadmin(List.of(dg, df, drh, dada));
    log.info("Organisations : DG/DF/DRH/DADA vérifiées — superadmin rattaché à chaque bureau.");
  }

  private Organisation organisation(String code, String name, String description, Long parentId) {
    return this.organisationRepository
        .findAll((root, q, cb) -> cb.equal(root.get("code"), code))
        .stream()
        .findFirst()
        .orElseGet(() -> {
          Organisation o = new Organisation();
          o.setCode(code);
          o.setName(name);
          o.setDescription(description);
          o.setParentId(parentId);
          return this.organisationRepository.save(o);
        });
  }

  private void ensureSuperadmin(List<Organisation> organisations) {
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

    User superadminUser =
        this.userRepository.findByEmail(SUPERADMIN_EMAIL).orElseGet(() -> {
          User u = new User();
          u.setEmail(SUPERADMIN_EMAIL);
          u.setName("Super");
          u.setSurname("Admin");
          u.setBirthday(LocalDate.of(1990, 1, 1));
          u.setPassword(this.passwordEncoder.encode(SUPERADMIN_PASSWORD));
          u.setCurrentOrganisationId(organisations.get(0).getId());
          return this.userRepository.save(u);
        });

    // Rôle global superadmin (user_roles) — lu par `PermissionService.isSuperAdmin()`.
    if (!this.userRoleRepository.findRoleNamesByUserId(superadminUser.getId()).contains("superadmin")) {
      UserRole ur = new UserRole();
      ur.setUserId(superadminUser.getId());
      ur.setRoleId(superadmin.getId());
      this.userRoleRepository.save(ur);
    }

    // Rattachement à chaque bureau (user_organisation_role).
    for (Organisation org : organisations) {
      if (!this.userOrganisationRoleRepository.existsById(
          new UserOrganisationRoleId(superadminUser.getId(), org.getId()))) {
        UserOrganisationRole uor = new UserOrganisationRole();
        uor.setId(new UserOrganisationRoleId(superadminUser.getId(), org.getId()));
        uor.setRoleId(superadmin.getId());
        uor.setCreatorId(superadminUser.getId());
        this.userOrganisationRoleRepository.save(uor);
      }
    }
  }
}
