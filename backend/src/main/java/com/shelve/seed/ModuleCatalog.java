package com.shelve.seed;

import java.util.List;

/**
 * Catalogue des modules installables. Quatre modules sont obligatoires
 * (records, transfers, tools, settings) ; les autres sont facultatifs.
 */
public final class ModuleCatalog {
  private ModuleCatalog() {}

  public record Module(String key, String label, String description, boolean required) {}

  public static final List<Module> ALL =
      List.of(
          new Module("records", "Notices / Records", "Gestion des notices, dossiers et documents (typologies, statuts, niveaux, supports, confidentialités, métadonnées).", true),
          new Module("transfers", "Transferts / Versements", "Bordereaux de versement, statuts de bordereaux, sorts finaux (E/T/C).", true),
          new Module("tools", "Outils / Référentiels", "Mots-clés, activités, domaines de valeurs (reference-lists).", true),
          new Module("settings", "Paramètres", "Paramètres applicatifs, sécurité, notifications et configuration IA.", true),
          new Module("workplace", "Workplaces", "Espaces de travail collaboratifs et leurs catégories.", false),
          new Module("workflow", "Workflow", "Définitions et instances de workflow (circuits d'approbation).", false),
          new Module("projects", "Projets", "Projets, objectifs, KPI et livrables.", false),
          new Module("ai", "IA / Assistant", "Prompts système, skills et templates de l'assistant IA.", false),
          new Module("email", "Messagerie", "Comptes de messagerie IMAP/SMTP (module Email).", false),
          new Module("mail", "Courrier", "Actions, priorités et typologies de courrier (D06).", false));
}
