package com.shelve.seed;

import com.shelve.localisation.entity.ContainerStatus;
import com.shelve.localisation.repository.ContainerStatusRepository;
import com.shelve.records.entity.RecordConfidentiality;
import com.shelve.records.entity.RecordLevel;
import com.shelve.records.entity.RecordStatus;
import com.shelve.records.entity.RecordSupport;
import com.shelve.records.repository.RecordConfidentialityRepository;
import com.shelve.records.repository.RecordLevelRepository;
import com.shelve.records.repository.RecordStatusRepository;
import com.shelve.records.repository.RecordSupportRepository;
import com.shelve.referentials.entity.Keyword;
import com.shelve.referentials.entity.Sort;
import com.shelve.referentials.repository.KeywordRepository;
import com.shelve.referentials.repository.SortRepository;
import com.shelve.slips.entity.SlipStatus;
import com.shelve.slips.repository.SlipStatusRepository;
import java.util.List;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Référentiels communs — transposition des seeders Laravel (RecordStatusSeeder,
 * RecordLevelSeeder, RecordSupportSeeder, SortSeeder, ContainerStatusSeeder,
 * SlipStatusSeeder, KeywordSeeder). Idempotent : ne crée que ce qui manque.
 * Aucune donnée métier (notices, courriers, …).
 *
 * NB : les catégories de workplace vivent dans {@link WorkplaceModuleSeeder}
 * (module facultatif) — elles ne sont PAS créées par les référentiels communs.
 */
@Component
public class ReferentialsSeeder {
  private static final Logger log = LoggerFactory.getLogger(ReferentialsSeeder.class);

  private final SortRepository sortRepository;
  private final RecordStatusRepository recordStatusRepository;
  private final RecordLevelRepository recordLevelRepository;
  private final RecordSupportRepository recordSupportRepository;
  private final RecordConfidentialityRepository recordConfidentialityRepository;
  private final ContainerStatusRepository containerStatusRepository;
  private final SlipStatusRepository slipStatusRepository;
  private final KeywordRepository keywordRepository;

  public ReferentialsSeeder(
      SortRepository sortRepository,
      RecordStatusRepository recordStatusRepository,
      RecordLevelRepository recordLevelRepository,
      RecordSupportRepository recordSupportRepository,
      RecordConfidentialityRepository recordConfidentialityRepository,
      ContainerStatusRepository containerStatusRepository,
      SlipStatusRepository slipStatusRepository,
      KeywordRepository keywordRepository) {
    this.sortRepository = sortRepository;
    this.recordStatusRepository = recordStatusRepository;
    this.recordLevelRepository = recordLevelRepository;
    this.recordSupportRepository = recordSupportRepository;
    this.recordConfidentialityRepository = recordConfidentialityRepository;
    this.containerStatusRepository = containerStatusRepository;
    this.slipStatusRepository = slipStatusRepository;
    this.keywordRepository = keywordRepository;
  }

  @Transactional
  public void run() {
    this.seedSorts();
    this.seedRecordStatuses();
    this.seedRecordLevels();
    this.seedRecordSupports();
    this.seedRecordConfidentialities();
    this.seedContainerStatuses();
    this.seedSlipStatuses();
    this.seedKeywords();
    log.info("Référentiels communs : OK (sorts, statuts, niveaux, supports, confidentialités, statuts contenants/bordereaux, mots-clés)");
  }

  private void seedSorts() {
    if (this.sortRepository.count() > 0) return;
    for (String[] row : List.of(
        new String[] {"E", "Élimination", "Documents destinés à être éliminés après expiration de la durée de rétention"},
        new String[] {"T", "Tri", "Documents nécessitant un tri pour déterminer ceux à conserver définitivement"},
        new String[] {"C", "Conservation", "Documents à conserver définitivement pour leur valeur historique ou administrative"})) {
      Sort s = new Sort();
      s.setCode(row[0]);
      s.setName(row[1]);
      s.setDescription(row[2]);
      this.sortRepository.save(s);
    }
    log.info("  sorts : {} créés (E/T/C)", 3);
  }

  private void seedRecordStatuses() {
    long before = this.recordStatusRepository.count();
    for (String[] row : List.of(
        new String[] {"Brouillon", "Document en cours de rédaction, non publié"},
        new String[] {"Validation", "Document en attente de validation"},
        new String[] {"Publié", "Document validé et publié"},
        new String[] {"Archivé", "Document archivé, visible mais non modifiable"},
        new String[] {"Supprimé", "Document supprimé logiquement"},
        new String[] {"Proposé à l'élimination", "Le dossier figure dans une liste de déclassement en cours de circuit d'approbation"},
        new String[] {"Éliminé", "Le dossier a été détruit à l'issue d'une liste de déclassement traitée"})) {
      if (this.recordStatusRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), row[0])).stream().findAny().isEmpty()) {
        RecordStatus s = new RecordStatus();
        s.setName(row[0]);
        s.setDescription(row[1]);
        this.recordStatusRepository.save(s);
      }
    }
    log.info("  record_statuses : {} ajoutés (base {}", this.recordStatusRepository.count() - before, before + ")");
  }

  private void seedRecordLevels() {
    long before = this.recordLevelRepository.count();
    for (String[] row : List.of(
        new String[] {"Fonds", "Ensemble de documents de toute nature constitué de façon organique par un producteur", "true"},
        new String[] {"Sous-fonds", "Division organique d'un fonds correspondant aux divisions administratives de l'institution", "true"},
        new String[] {"Série", "Division d'un fonds suivant l'organisation du producteur ou le classement thématique", "true"},
        new String[] {"Sous-série", "Division d'une série", "true"},
        new String[] {"Dossier", "Ensemble de documents regroupés pour leur utilisation courante ou dans le processus d'organisation des archives", "true"},
        new String[] {"Pièce", "Plus petite unité archivistique indivisible (document, lettre, rapport, etc.)", "false"})) {
      if (this.recordLevelRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), row[0])).stream().findAny().isEmpty()) {
        RecordLevel l = new RecordLevel();
        l.setName(row[0]);
        l.setDescription(row[1]);
        l.setHasChild(Boolean.parseBoolean(row[2]));
        this.recordLevelRepository.save(l);
      }
    }
    log.info("  record_levels : {} ajoutés (base {}", this.recordLevelRepository.count() - before, before + ")");
  }

  private void seedRecordSupports() {
    long before = this.recordSupportRepository.count();
    for (String[] row : List.of(
        new String[] {"Papier", "Document sur support papier classique"},
        new String[] {"Parchemin", "Document sur parchemin (peau animale préparée)"},
        new String[] {"Microfilm", "Document reproduit sur microfilm"},
        new String[] {"Photographie", "Support photographique (négatif, diapositive, tirage)"},
        new String[] {"Bande magnétique", "Enregistrement sur bande magnétique (audio ou vidéo)"},
        new String[] {"Disque optique", "Support de type CD, DVD, Blu-ray"},
        new String[] {"Disquette", "Support de stockage informatique obsolète"},
        new String[] {"Numérique", "Document né-numérique sans support physique spécifique"},
        new String[] {"Carte et plan", "Document cartographique"},
        new String[] {"Objet", "Objet physique conservé comme archive"})) {
      if (this.recordSupportRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), row[0])).stream().findAny().isEmpty()) {
        RecordSupport s = new RecordSupport();
        s.setName(row[0]);
        s.setDescription(row[1]);
        this.recordSupportRepository.save(s);
      }
    }
    log.info("  record_supports : {} ajoutés (base {}", this.recordSupportRepository.count() - before, before + ")");
  }

  private void seedRecordConfidentialities() {
    if (this.recordConfidentialityRepository.count() > 0) return;
    for (String[] row : List.of(
        new String[] {"PUB", "Public"},
        new String[] {"INT", "Interne"},
        new String[] {"RES", "Restreint"},
        new String[] {"CON", "Confidentiel"},
        new String[] {"SEC", "Secret"})) {
      RecordConfidentiality c = new RecordConfidentiality();
      c.setCode(row[0]);
      c.setName(row[1]);
      this.recordConfidentialityRepository.save(c);
    }
    log.info("  record_confidentialities : {} créées", 5);
  }

  private void seedContainerStatuses() {
    if (this.containerStatusRepository.count() > 0) return;
    for (String[] row : List.of(
        new String[] {"Actif", "Contenant en cours d'utilisation"},
        new String[] {"Archivé", "Contenant archivé et non modifiable"},
        new String[] {"En transit", "Contenant en cours de transfert"},
        new String[] {"Temporaire", "Contenant temporaire en attente de traitement"},
        new String[] {"Détruit", "Contenant détruit selon les règles de conservation"})) {
      ContainerStatus s = new ContainerStatus();
      s.setName(row[0]);
      s.setDescription(row[1]);
      s.setCreatorId(1L);
      this.containerStatusRepository.save(s);
    }
    log.info("  container_statuses : {} créés", 5);
  }

  private void seedSlipStatuses() {
    for (String[] row : List.of(
        new String[] {"Projects", "Bordereau en projet, non encore finalisé"},
        new String[] {"Received", "Bordereau reçu par l'organisme destinataire"},
        new String[] {"Approved", "Bordereau approuvé"},
        new String[] {"Integrated", "Bordereau intégré dans le système d'archivage"})) {
      if (this.slipStatusRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), row[0])).stream().findAny().isEmpty()) {
        SlipStatus s = new SlipStatus();
        s.setName(row[0]);
        s.setDescription(row[1]);
        this.slipStatusRepository.save(s);
      }
    }
    log.info("  slip_statuses : vérifiés (Projects/Received/Approved/Integrated)");
  }

  private void seedKeywords() {
    long before = this.keywordRepository.count();
    List<String> keywords = List.of(
        "RH", "Recrutement", "Formation", "Paie", "Contrat", "Salaire", "Employé",
        "Candidature", "Entretien", "Compétences", "Évaluation", "Carrière",
        "Comptabilité", "Facturation", "Budget", "Dépenses", "Recettes", "Finances",
        "Fournisseur", "Client", "Paiement", "Trésorerie", "Bilan", "Compte",
        "Projet", "Analyse", "Étude", "Rapport", "Planification", "Suivi",
        "Livrable", "Jalon", "Risque", "Budget projet", "Équipe",
        "Documentation", "Archive", "Numérique", "Digital", "Document",
        "Dossier", "Classement", "Conservation", "Gestion documentaire",
        "Transformation", "Innovation", "Stratégie", "Management", "Processus",
        "Workflow", "Qualité", "Performance", "Amélioration", "Optimisation",
        "Méthodologie", "Procédure", "Norme", "Standard", "Référentiel",
        "Guide", "Manuel", "Instruction", "Consigne",
        "Communication", "Information", "Diffusion", "Publication", "Notification",
        "Annonce", "Communiqué", "Note de service",
        "Juridique", "Légal", "Réglementaire", "Conformité", "Audit",
        "Contrôle", "Vérification", "Certification");
    for (String name : keywords) {
      if (this.keywordRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), name)).stream().findAny().isEmpty()) {
        Keyword k = new Keyword();
        k.setName(name);
        this.keywordRepository.save(k);
      }
    }
    log.info("  keywords : {} ajoutés (base {}", this.keywordRepository.count() - before, before + ")");
  }
}
