package com.shelve.seed;

import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.stereotype.Component;

/** Propriétés du seeder — `app.seed.enabled` (true = exécution au démarrage). */
@Component
@ConfigurationProperties(prefix = "app.seed")
public class SeedProperties {
  private boolean enabled = false;

  public boolean isEnabled() {
    return this.enabled;
  }

  public void setEnabled(boolean enabled) {
    this.enabled = enabled;
  }
}
