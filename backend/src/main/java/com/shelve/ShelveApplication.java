package com.shelve;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.properties.EnableConfigurationProperties;
import com.shelve.ai.sandbox.config.SandboxProperties;

/**
 * Point d'entr�e du backend Spring Boot - phase 3 de la migration shelve.
 *
 * <p>Attaque la m�me base MySQL que Laravel (coexistence, �3.0.1) : pas de reprise de donn�es,
 * diff-testing et rollback possibles.
 */
@SpringBootApplication
@EnableConfigurationProperties(SandboxProperties.class)
public class ShelveApplication {

  public static void main(String[] args) {
    SpringApplication.run(ShelveApplication.class, args);
  }
}
