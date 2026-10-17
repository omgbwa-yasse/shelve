package com.shelve.seed;

import com.shelve.collaboration.entity.WorkplaceCategory;
import com.shelve.collaboration.repository.WorkplaceCategoryRepository;
import java.util.List;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Module Workplaces (facultatif) — catégories d'espaces de travail
 * (transposition de `Workplaces/WorkplaceCategorySeeder.php`). Idempotent.
 *
 * Ce seeder n'est exécuté que si le module `workplace` est coché pendant
 * l'installation — contrairement aux référentiels communs (records/transfers/tools).
 */
@Component
public class WorkplaceModuleSeeder {
  private static final Logger log = LoggerFactory.getLogger(WorkplaceModuleSeeder.class);

  private final WorkplaceCategoryRepository repository;

  public WorkplaceModuleSeeder(WorkplaceCategoryRepository repository) {
    this.repository = repository;
  }

  @Transactional
  public void run() {
    long before = this.repository.count();
    for (String[] row : List.of(
        new String[] {"GENERAL", "Général", "Espace de travail général", "briefcase", "#3498db", "0"},
        new String[] {"FINANCE", "Finance & Comptabilité", "Espace de travail pour la gestion financière, comptabilité et budget.", "cash-stack", "#27ae60", "1"},
        new String[] {"IT", "Informatique & Technologie", "Espace de travail pour les projets IT, développement et infrastructure.", "pc-display", "#2980b9", "2"},
        new String[] {"LEGAL", "Juridique", "Espace de travail pour les affaires juridiques, contrats et conformité.", "shield-check", "#8e44ad", "3"},
        new String[] {"COMM", "Communication", "Espace de travail pour la communication interne et externe, relations publiques.", "megaphone", "#f39c12", "4"},
        new String[] {"PROJECT", "Gestion de Projet", "Espace de travail pour la gestion et le suivi de projets transversaux.", "kanban", "#1abc9c", "5"},
        new String[] {"ARCHIVE", "Archives", "Espace de travail dédié à la gestion et conservation des archives.", "archive", "#95a5a6", "6"},
        new String[] {"ADMIN", "Administration Générale", "Espace de travail pour l'administration et la direction générale.", "building", "#34495e", "7"},
        new String[] {"OTHER", "Autre", "Espace de travail à usage général ou non classifié.", "folder2-open", "#7f8c8d", "98"})) {
      String code = row[0];
      if (this.repository.findByCode(code).isEmpty()) {
        WorkplaceCategory c = new WorkplaceCategory();
        c.setCode(code);
        c.setName(row[1]);
        c.setDescription(row[2]);
        c.setIcon(row[3]);
        c.setColor(row[4]);
        c.setIsActive(true);
        c.setDisplayOrder(Integer.parseInt(row[5]));
        this.repository.save(c);
      }
    }
    log.info("Module Workplaces : catégories vérifiées (base {})", before);
  }
}
