package com.shelve.seed;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.stereotype.Component;

/**
 * Seeder du backend Spring Boot — transposition des seeders Laravel
 * (`laravel/database/seeders`). S'exécute au démarrage quand `app.seed.enabled`
 * vaut `true` (voir application.yml / application-test.yml).
 *
 * Socle fonctionnel uniquement, PAS de données métier :
 *  - référentiels de base (sorts, statuts, niveaux, supports, confidentialités,
 *    statuts contenants/bordereaux, mots-clés, catégories workplace) ;
 *  - paramètres applicatifs (settings + catégories) ;
 *  - comptes d'accès (rôle superadmin + admin@example.com).
 *
 * Idempotent : ne crée que ce qui manque — rejouable sur shelve_db comme sur
 * shelve_test sans doublon ni écrasement.
 */
@Component
public class DatabaseSeeder implements ApplicationRunner {
  private static final Logger log = LoggerFactory.getLogger(DatabaseSeeder.class);

  private final SeedProperties properties;
  private final ReferentialsSeeder referentialsSeeder;
  private final SettingsSeeder settingsSeeder;
  private final OrganisationSeeder organisationSeeder;
  private final WorkplaceModuleSeeder workplaceModuleSeeder;
  private final AdminSeeder adminSeeder;
  private final ConstellioAlignmentSeeder constellioAlignmentSeeder;
  private final LifecycleSeeder lifecycleSeeder;

  public DatabaseSeeder(
      SeedProperties properties,
      ReferentialsSeeder referentialsSeeder,
      SettingsSeeder settingsSeeder,
      OrganisationSeeder organisationSeeder,
      WorkplaceModuleSeeder workplaceModuleSeeder,
      AdminSeeder adminSeeder,
      ConstellioAlignmentSeeder constellioAlignmentSeeder,
      LifecycleSeeder lifecycleSeeder) {
    this.properties = properties;
    this.referentialsSeeder = referentialsSeeder;
    this.settingsSeeder = settingsSeeder;
    this.organisationSeeder = organisationSeeder;
    this.workplaceModuleSeeder = workplaceModuleSeeder;
    this.adminSeeder = adminSeeder;
    this.constellioAlignmentSeeder = constellioAlignmentSeeder;
    this.lifecycleSeeder = lifecycleSeeder;
  }

  @Override
  public void run(ApplicationArguments args) {
    if (!this.properties.isEnabled()) {
      log.info("Seeding désactivé (app.seed.enabled=false).");
      return;
    }
    log.info("=== Seeding du socle Spring Boot (idempotent) ===");
    this.referentialsSeeder.run();
    this.settingsSeeder.run();
    this.organisationSeeder.run();
    this.workplaceModuleSeeder.run();
    this.adminSeeder.run();
    this.constellioAlignmentSeeder.run();
    this.lifecycleSeeder.run();
    log.info("=== Seeding terminé ===");
  }
}
