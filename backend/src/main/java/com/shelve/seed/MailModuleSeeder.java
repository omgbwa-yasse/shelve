package com.shelve.seed;

import com.shelve.mails.entity.MailAction;
import com.shelve.mails.entity.MailPriority;
import com.shelve.mails.entity.MailTypology;
import com.shelve.mails.repository.MailActionRepository;
import com.shelve.mails.repository.MailPriorityRepository;
import com.shelve.mails.repository.MailTypologyRepository;
import com.shelve.referentials.entity.Activity;
import com.shelve.referentials.repository.ActivityRepository;
import java.util.List;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Module Courrier (D06) — transposition de `Mails/MailSystemSeeder.php` :
 * priorités, actions et typologies de courrier. Idempotent.
 */
@Component
public class MailModuleSeeder {
  private static final Logger log = LoggerFactory.getLogger(MailModuleSeeder.class);

  private final MailActionRepository actionRepository;
  private final MailPriorityRepository priorityRepository;
  private final MailTypologyRepository typologyRepository;
  private final ActivityRepository activityRepository;

  public MailModuleSeeder(
      MailActionRepository actionRepository,
      MailPriorityRepository priorityRepository,
      MailTypologyRepository typologyRepository,
      ActivityRepository activityRepository) {
    this.actionRepository = actionRepository;
    this.priorityRepository = priorityRepository;
    this.typologyRepository = typologyRepository;
    this.activityRepository = activityRepository;
  }

  @Transactional
  public void run() {
    this.priorities();
    this.actions();
    this.typologies();
    log.info("Module Courrier (D06) : priorités, actions et typologies vérifiés.");
  }

  private void priorities() {
    for (String[] row : List.of(
        new String[] {"Très urgent", "1"},
        new String[] {"Urgent", "3"},
        new String[] {"Normal", "7"},
        new String[] {"Faible", "15"},
        new String[] {"Informationnel", "30"})) {
      if (this.priorityRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), row[0])).stream().findAny().isEmpty()) {
        MailPriority p = new MailPriority();
        p.setName(row[0]);
        p.setDuration(Integer.parseInt(row[1]));
        this.priorityRepository.save(p);
      }
    }
  }

  private void actions() {
    for (Object[] row : List.of(
        new Object[] {"Pour information", 0, false, "Courrier transmis à titre informatif uniquement"},
        new Object[] {"Pour avis", 5, true, "Demande d'avis ou de consultation"},
        new Object[] {"Pour décision", 7, true, "Nécessite une prise de décision"},
        new Object[] {"Pour signature", 3, true, "Document à signer"},
        new Object[] {"Pour validation", 5, true, "Document à valider ou approuver"},
        new Object[] {"Pour traitement", 10, true, "Dossier nécessitant un traitement complet"},
        new Object[] {"Pour suivi", 15, false, "Dossier à suivre régulièrement"},
        new Object[] {"Pour archivage", 0, false, "Document à archiver"})) {
      String name = (String) row[0];
      if (this.actionRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), name)).stream().findAny().isEmpty()) {
        MailAction a = new MailAction();
        a.setName(name);
        a.setDuration((Integer) row[1]);
        a.setToReturn((Boolean) row[2]);
        a.setDescription((String) row[3]);
        this.actionRepository.save(a);
      }
    }
  }

  private void typologies() {
    Activity defaultActivity =
        this.activityRepository.findAll().stream().findFirst().orElse(null);
    for (String[] row : List.of(
        new String[] {"CORR", "Correspondance générale", "Courrier de correspondance générale"},
        new String[] {"INFO", "Information", "Courrier d'information"},
        new String[] {"CONV", "Convention", "Convention, accord ou protocole"},
        new String[] {"NOTE", "Note de service", "Note interne de service"},
        new String[] {"REQ", "Réquisition", "Demande, requête ou sollicitation"},
        new String[] {"RAP", "Rapport", "Rapport ou compte rendu"},
        new String[] {"FACT", "Facture", "Facture ou note d'honoraires"},
        new String[] {"ARR", "Arrêté", "Arrêté ou décision administrative"})) {
      String code = row[0];
      if (this.typologyRepository.findAll((root, q, cb) -> cb.equal(root.get("code"), code)).stream().findAny().isEmpty()) {
        MailTypology t = new MailTypology();
        t.setCode(code);
        t.setName(row[1]);
        t.setDescription(row[2]);
        t.setActivityId(defaultActivity != null ? defaultActivity.getId() : null);
        this.typologyRepository.save(t);
      }
    }
  }
}
