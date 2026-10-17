package com.shelve.opac.service;

import com.shelve.exception.ValidationException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

/**
 * Registre des modules OPAC — équivalent Java de `config/opac-modules.php`.
 * Source unique du catalogue : l'API `/api/v1/opac/modules` le sert tel quel,
 * et toute mise à jour d'une version revalide ses blocs contre ce registre
 * (module inconnu, variante interdite, zone non autorisée → 422).
 */
public final class OpacModuleRegistry {

  private static final Map<String, Map<String, Object>> MODULES = buildModules();

  private OpacModuleRegistry() {}

  // ============================ HELPERS ============================

  private static Map<String, Object> m(Object... kv) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    for (int i = 0; i < kv.length; i += 2) {
      map.put((String) kv[i], kv[i + 1]);
    }
    return map;
  }

  private static Map<String, Object> opt(String value, String label) {
    return m("value", value, "label", label);
  }

  private static Map<String, Object> variant(String key, String label) {
    return m("key", key, "label", label);
  }

  private static Map<String, Object> setting(
      String key, String label, String type, Object def, boolean responsive, Object... extra) {
    LinkedHashMap<String, Object> s =
        new LinkedHashMap<String, Object>();
    s.put("key", key);
    s.put("label", label);
    s.put("type", type);
    s.put("default", def);
    s.put("responsive", responsive);
    for (int i = 0; i < extra.length; i += 2) {
      s.put((String) extra[i], extra[i + 1]);
    }
    return s;
  }

  // ============================ CATALOGUE ============================

  public static List<Map<String, Object>> zones() {
    return List.of(
        m("key", "header", "label", "En-tête"),
        m("key", "hero", "label", "Bandeau"),
        m("key", "main", "label", "Contenu principal"),
        m("key", "sidebar", "label", "Colonne latérale"),
        m("key", "footer", "label", "Pied de page"));
  }

  public static List<String> zoneKeys() {
    List<String> keys = new ArrayList<String>();
    for (Map<String, Object> z : zones()) {
      keys.add((String) z.get("key"));
    }
    return keys;
  }

  public static List<Map<String, Object>> breakpoints() {
    return List.of(
        m("key", "mobile", "label", "Mobile"),
        m("key", "tablet", "label", "Tablette"),
        m("key", "desktop", "label", "PC"));
  }

  public static Map<String, Map<String, Object>> all() {
    return MODULES;
  }

  public static Map<String, Object> get(String key) {
    return MODULES.get(key);
  }

  public static boolean has(String key) {
    return MODULES.containsKey(key);
  }

  private static Map<String, Map<String, Object>> buildModules() {
    Map<String, Map<String, Object>> modules = new LinkedHashMap<String, Map<String, Object>>();

    modules.put(
        "menu",
        m(
            "label",
            "Menu de navigation",
            "description",
            "Navigation principale du portail (rubriques des pages publiées).",
            "icon",
            "menu",
            "zones",
            List.of("header", "footer"),
            "variants",
            List.of(variant("horizontal", "Horizontale"), variant("vertical", "Verticale"), variant("burger", "Burger (mobile)")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Navigation", false),
                setting("show_sub", "Afficher les sous-rubriques", "boolean", true, false)),
            "source",
            m("type", "pages")));

    modules.put(
        "search",
        m(
            "label",
            "Recherche",
            "description",
            "Barre de recherche dans le catalogue.",
            "icon",
            "search",
            "zones",
            List.of("header", "hero", "main", "sidebar"),
            "variants",
            List.of(variant("simple", "Simple"), variant("advanced", "Avancée")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Rechercher", false),
                setting("placeholder", "Texte d'aide", "text", "Rechercher un document…", false)),
            "source",
            m("type", "search")));

    modules.put(
        "records",
        m(
            "label",
            "Notices (catalogue)",
            "description",
            "Sélection de notices du catalogue : liste, mosaïque, carrousel ou vedette.",
            "icon",
            "library",
            "zones",
            List.of("hero", "main", "sidebar"),
            "variants",
            List.of(variant("grid", "Mosaïque"), variant("list", "Liste"), variant("carousel", "Carrousel"), variant("featured", "Vedette")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Derniers documents", false),
                setting("limit", "Nombre d'éléments", "number", 4, true, "min", 1, "max", 24),
                setting("cols", "Colonnes", "number", 3, true, "min", 1, "max", 4),
                setting(
                    "sort",
                    "Tri",
                    "select",
                    "created_at",
                    false,
                    "options",
                    List.of(opt("created_at", "Plus récents"), opt("title", "Titre (A → Z)"))),
                setting("has_digital_copy", "Documents numériques uniquement", "boolean", false, false)),
            "source",
            m(
                "endpoint",
                "/api/public/records",
                "params",
                m("per_page", "limit", "sort_by", "sort", "has_digital_copy", "has_digital_copy"))));

    modules.put(
        "news",
        m(
            "label",
            "Actualités",
            "description",
            "Dernières actualités publiées du portail.",
            "icon",
            "newspaper",
            "zones",
            List.of("hero", "main", "sidebar", "footer"),
            "variants",
            List.of(variant("grid", "Grille / mosaïque"), variant("list", "Liste"), variant("featured", "À la une")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Actualités", false),
                setting("limit", "Nombre d'éléments", "number", 3, true, "min", 1, "max", 12),
                setting("cols", "Colonnes", "number", 3, true, "min", 1, "max", 4),
                setting("featured_only", "Articles à la une uniquement", "boolean", false, false)),
            "source",
            m("endpoint", "/api/public/news", "params", m("per_page", "limit", "featured", "featured_only"))));

    modules.put(
        "events",
        m(
            "label",
            "Événements",
            "description",
            "Événements du portail : liste chronologique, agenda ou prochains rendez-vous.",
            "icon",
            "calendarDays",
            "zones",
            List.of("hero", "main", "sidebar"),
            "variants",
            List.of(variant("list", "Liste chronologique"), variant("upcoming", "À venir"), variant("calendar", "Agenda")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Événements", false),
                setting("limit", "Nombre d'éléments", "number", 4, true, "min", 1, "max", 12),
                setting("cols", "Colonnes", "number", 2, true, "min", 1, "max", 3),
                setting(
                    "filter",
                    "Période",
                    "select",
                    "upcoming",
                    false,
                    "options",
                    List.of(
                        opt("upcoming", "À venir"),
                        opt("today", "Aujourd'hui"),
                        opt("this_week", "Cette semaine"),
                        opt("this_month", "Ce mois")))),
                "source",
                m("endpoint", "/api/public/events", "params", m("per_page", "limit", "filter", "filter"))));

    modules.put(
        "calendar",
        m(
            "label",
            "Calendrier",
            "description",
            "Calendrier des événements (vue mois ou agenda).",
            "icon",
            "calendar",
            "zones",
            List.of("main", "sidebar"),
            "variants",
            List.of(variant("month", "Vue mois"), variant("agenda", "Agenda")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Agenda", false),
                setting(
                    "filter",
                    "Période",
                    "select",
                    "this_month",
                    false,
                    "options",
                    List.of(
                        opt("upcoming", "À venir"),
                        opt("this_week", "Cette semaine"),
                        opt("this_month", "Ce mois")))),
                "source",
                m("endpoint", "/api/public/events", "params", m("per_page", "limit", "filter", "filter"))));

    modules.put(
        "document",
        m(
            "label",
            "Documents numériques",
            "description",
            "Documents numériques disponibles : liste, vignettes ou visionneuse.",
            "icon",
            "fileDigit",
            "zones",
            List.of("main", "sidebar"),
            "variants",
            List.of(variant("grid", "Vignettes"), variant("list", "Liste"), variant("viewer", "Visionneuse")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Documents numériques", false),
                setting("limit", "Nombre d'éléments", "number", 6, true, "min", 1, "max", 24),
                setting("cols", "Colonnes", "number", 3, true, "min", 1, "max", 4)),
            "source",
            m(
                "endpoint",
                "/api/public/records",
                "params",
                m("per_page", "limit"),
                "fixed",
                m("has_digital_copy", true))));

    modules.put(
        "rss",
        m(
            "label",
            "Flux RSS",
            "description",
            "Affiche un flux RSS externe sous forme de liste.",
            "icon",
            "rss",
            "zones",
            List.of("main", "sidebar", "footer"),
            "variants",
            List.of(variant("list", "Liste")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Fil d'actualité", false),
                setting("url", "URL du flux", "url", "", false),
                setting("limit", "Nombre d'éléments", "number", 5, true, "min", 1, "max", 20)),
            "source",
            m("type", "rss")));

    modules.put(
        "feedback",
        m(
            "label",
            "Avis",
            "description",
            "Retours / avis du portail : liste ou formulaire.",
            "icon",
            "star",
            "zones",
            List.of("main", "sidebar", "footer"),
            "variants",
            List.of(variant("list", "Liste des avis"), variant("form", "Formulaire")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Votre avis", false),
                setting("limit", "Nombre d'éléments", "number", 3, true, "min", 1, "max", 10)),
            "source",
            m("type", "feedback")));

    modules.put(
        "banner",
        m(
            "label",
            "Bannière",
            "description",
            "Image large avec titre et lien optionnel.",
            "icon",
            "image",
            "zones",
            List.of("hero", "main", "footer"),
            "variants",
            List.of(variant("full", "Pleine largeur"), variant("column", "Colonne")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "", false),
                setting("image", "Image (URL)", "url", "", false),
                setting("link", "Lien", "url", "", false),
                setting(
                    "height",
                    "Hauteur",
                    "select",
                    "md",
                    false,
                    "options",
                    List.of(opt("sm", "Petite"), opt("md", "Moyenne"), opt("lg", "Grande")))),
            "source",
            null));

    modules.put(
        "text",
        m(
            "label",
            "Texte / HTML",
            "description",
            "Bloc de contenu libre.",
            "icon",
            "fileText",
            "zones",
            List.of("hero", "main", "sidebar", "footer"),
            "variants",
            List.of(variant("rich", "Riche")),
            "settings",
            List.of(setting("title", "Titre", "text", "", false), setting("html", "Contenu HTML", "textarea", "", false)),
            "source",
            null));

    modules.put(
        "links",
        m(
            "label",
            "Liens",
            "description",
            "Liste de liens (format : Libellé | URL, un par ligne).",
            "icon",
            "links",
            "zones",
            List.of("sidebar", "footer"),
            "variants",
            List.of(variant("list", "Liste")),
            "settings",
            List.of(
                setting("title", "Titre", "text", "Liens utiles", false),
                setting("links", "Liens (libellé | URL)", "textarea", "", false)),
            "source",
            null));

    return modules;
  }

  // ============================ OPÉRATIONS ============================

  /** Réglages par défaut d'un module (clé → valeur). */
  public static Map<String, Object> defaults(String moduleKey) {
    Map<String, Object> defaults = new LinkedHashMap<String, Object>();
    Map<String, Object> module = get(moduleKey);
    if (module == null) {
      return defaults;
    }
    @SuppressWarnings("unchecked")
    List<Map<String, Object>> settings = (List<Map<String, Object>>) module.get("settings");
    if (settings != null) {
      for (Map<String, Object> s : settings) {
        defaults.put((String) s.get("key"), s.get("default"));
      }
    }
    return defaults;
  }

  /** Schéma d'un module exposé à l'éditeur (avec sa clé). */
  public static Map<String, Object> moduleSchema(String key) {
    Map<String, Object> module = get(key);
    if (module == null) {
      return null;
    }
    LinkedHashMap<String, Object> schema = new LinkedHashMap<String, Object>();
    schema.put("key", key);
    schema.putAll(module);
    return schema;
  }

  /**
   * Normaliser + valider un bloc reçu contre le registre. Lève une
   * ValidationException (422) si le module, la zone ou la variante est
   * invalide. Renvoie le bloc normalisé (défauts fusionnés).
   */
  @SuppressWarnings("unchecked")
  public static Map<String, Object> normalize(Map<String, Object> block, List<String> zones) {
    String moduleKey = block.get("module_key") != null ? String.valueOf(block.get("module_key")) : null;
    String zone = block.get("zone") != null ? String.valueOf(block.get("zone")) : null;

    LinkedHashMap<String, List<String>> errors = new LinkedHashMap<String, List<String>>();

    if (moduleKey == null || !has(moduleKey)) {
      errors.put("module_key", List.of("Module « " + moduleKey + " » inconnu."));
      throw ValidationException.of(errors);
    }

    Map<String, Object> module = get(moduleKey);

    @SuppressWarnings("unchecked")
    List<String> moduleZones =
        module.get("zones") instanceof List ? (List<String>) module.get("zones") : List.of();
    if (zone == null || !moduleZones.contains(zone)) {
      errors.put("zone", List.of("Le module « " + moduleKey + " » n'est pas autorisé dans la zone « " + zone + " »."));
    }

    Map<String, Object> settings =
        block.get("settings") instanceof Map ? (Map<String, Object>) block.get("settings") : new LinkedHashMap<String, Object>();
    Object variant = settings.get("variant");
    if (variant != null) {
      List<String> variantKeys = new ArrayList<String>();
      for (Object v : (List<Object>) module.getOrDefault("variants", List.of())) {
        variantKeys.add(String.valueOf(((Map<String, Object>) v).get("key")));
      }
      if (!variantKeys.contains(String.valueOf(variant))) {
        errors.put("variant", List.of("Variante « " + variant + " » inconnue pour « " + moduleKey + " »."));
      }
    }

    if (!errors.isEmpty()) {
      throw ValidationException.of(errors);
    }

    Map<String, Object> mergedSettings = new LinkedHashMap<String, Object>(defaults(moduleKey));
    mergedSettings.putAll(settings);

    LinkedHashMap<String, Object> normalized = new LinkedHashMap<String, Object>();
    normalized.put("module_key", moduleKey);
    normalized.put("zone", zone);
    normalized.put("settings", mergedSettings);
    normalized.put("responsive", block.get("responsive"));
    normalized.put(
        "order",
        block.get("order") instanceof Number ? ((Number) block.get("order")).intValue() : 0);
    return normalized;
  }

  /** Champs surchargeables par breakpoint pour un module. */
  public static Set<String> responsiveKeys(String moduleKey) {
    Set<String> keys = new LinkedHashSet<String>();
    Map<String, Object> module = get(moduleKey);
    if (module == null) {
      return keys;
    }
    @SuppressWarnings("unchecked")
    List<Map<String, Object>> settings = (List<Map<String, Object>>) module.get("settings");
    if (settings != null) {
      for (Map<String, Object> s : settings) {
        if (Boolean.TRUE.equals(s.get("responsive"))) {
          keys.add((String) s.get("key"));
        }
      }
    }
    return keys;
  }
}
