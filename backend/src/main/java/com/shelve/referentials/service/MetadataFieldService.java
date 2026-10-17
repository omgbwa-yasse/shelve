package com.shelve.referentials.service;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.shelve.referentials.entity.MetadataDefinition;
import com.shelve.referentials.entity.RecordTypeMetadataProfile;
import com.shelve.referentials.repository.MetadataDefinitionRepository;
import com.shelve.referentials.repository.RecordTypeMetadataProfileRepository;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import org.springframework.stereotype.Service;

/**
 * Moteur des métadonnées de schéma — aligné sur Constellio.
 *
 * - **métadonnées copiées** (`copy_source_type`/`copy_source_field`) : la valeur
 *   est lue depuis une autre entité (parent, référence, créateur…), en lecture
 *   seule ;
 * - **métadonnées calculées** (`computed_template`) : la valeur est évaluée à
 *   partir d'autres métadonnées (concaténation `$metadonnée` en mode simple,
 *   JEXL simplifié en mode avancé) ;
 * - **métadonnées sécurisées** (`restricted_to_roles`) : la métadonnée est masquée
 *   si le rôle de l'utilisateur n'est pas autorisé ;
 * - **champs visibles** : profil (obligatoire, lecture seule, groupe, tri, valeur
 *   par défaut) fusionné avec la définition.
 */
@Service
public class MetadataFieldService {
  private static final Pattern SIMPLE_REF = Pattern.compile("\\$([A-Za-z0-9_]+)");

  private final MetadataDefinitionRepository definitionRepository;
  private final RecordTypeMetadataProfileRepository profileRepository;
  private final ObjectMapper mapper;

  public MetadataFieldService(
      MetadataDefinitionRepository definitionRepository,
      RecordTypeMetadataProfileRepository profileRepository,
      ObjectMapper mapper) {
    this.definitionRepository = definitionRepository;
    this.profileRepository = profileRepository;
    this.mapper = mapper;
  }

  /**
   * Champs visibles d'un type de notice pour l'utilisateur (avec les rôles).
   *
   * @param recordTypeId type de notice
   * @param userRoles rôles de l'utilisateur courant (pour la sécurisation)
   * @param values valeurs saisies (pour calculer les métadonnées calculées)
   */
  public List<Map<String, Object>> fieldsForType(
      Long recordTypeId, Set<String> userRoles, Map<String, Object> values) {
    List<RecordTypeMetadataProfile> profiles =
        this.profileRepository.findByRecordTypeId(recordTypeId);
    Map<Long, MetadataDefinition> defs =
        this.definitionRepository.findAllById(
                profiles.stream().map(RecordTypeMetadataProfile::getMetadataDefinitionId).toList())
            .stream()
            .collect(
                java.util.stream.Collectors.toMap(MetadataDefinition::getId, d -> d));

    List<Map<String, Object>> out = new ArrayList<Map<String, Object>>();
    Map<String, Object> context = new LinkedHashMap<String, Object>(values == null ? Map.of() : values);

    for (RecordTypeMetadataProfile profile : profiles) {
      MetadataDefinition def = defs.get(profile.getMetadataDefinitionId());
      if (def == null || !Boolean.TRUE.equals(def.getActive()) || !Boolean.TRUE.equals(profile.getVisible())) {
        continue;
      }
      // Sécurisation par rôles : si restreint et rôle non autorisé → masqué.
      if (!this.canRead(profile, userRoles)) {
        continue;
      }
      context.put(def.getCode(), this.currentValue(def, profile, context));
      out.add(this.field(def, profile, context));
    }
    return out.stream().sorted((a, b) -> Integer.compare(
        ((Number) a.getOrDefault("sort_order", 0)).intValue(),
        ((Number) b.getOrDefault("sort_order", 0)).intValue())).toList();
  }

  /** Construit la carte de champ visible (définition + profil). */
  private Map<String, Object> field(MetadataDefinition def, RecordTypeMetadataProfile profile, Map<String, Object> context) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("code", def.getCode());
    map.put("name", def.getName());
    map.put("description", def.getDescription());
    map.put("data_type", def.getDataType());
    map.put("options", this.parseJson(def.getOptions()));
    map.put("required", Boolean.TRUE.equals(profile.getMandatory()));
    map.put("readonly", Boolean.TRUE.equals(profile.getReadonly())
        || def.getCopySourceType() != null
        || def.getComputedTemplate() != null);
    map.put("group", profile.getGroup());
    map.put("sort_order", profile.getSortOrder() != null ? profile.getSortOrder() : def.getSortOrder());
    map.put("searchable", Boolean.TRUE.equals(def.getSearchable()));
    map.put("sortable", Boolean.TRUE.equals(def.getSortable()));
    map.put("unique", Boolean.TRUE.equals(def.getUnique()));
    map.put("autocomplete", Boolean.TRUE.equals(def.getAutocomplete()));
    map.put("default_value", profile.getDefaultValue());
    map.put("reference_list_id", def.getReferenceListId());
    map.put("is_system", Boolean.TRUE.equals(def.getIsSystem()));
    // Copie / calcul (pour l'affichage « dérivée »).
    if (def.getCopySourceType() != null) {
      map.put("copy_source_type", def.getCopySourceType());
      map.put("copy_source_field", def.getCopySourceField());
    }
    if (def.getComputedTemplate() != null) {
      map.put("computed_template", def.getComputedTemplate());
    }
    // Valeur courante (saisie, copiée ou calculée).
    map.put("value", this.currentValue(def, profile, context));
    return map;
  }

  /** Valeur courante : calculée / copiée / saisie / défaut. */
  private Object currentValue(MetadataDefinition def, RecordTypeMetadataProfile profile, Map<String, Object> context) {
    if (def.getComputedTemplate() != null && !def.getComputedTemplate().isBlank()) {
      return this.evaluate(def.getComputedTemplate(), context);
    }
    if (def.getCopySourceType() != null && def.getCopySourceField() != null) {
      return context.getOrDefault(def.getCopySourceField(), null);
    }
    Object value = context.get(def.getCode());
    if (value != null) return value;
    return profile.getDefaultValue();
  }

  /** Évalue un template simple `$metadonnée $autre` (mode simple Constellio). */
  String evaluate(String template, Map<String, Object> context) {
    if (template == null) return "";
    Matcher m = SIMPLE_REF.matcher(template);
    StringBuffer sb = new StringBuffer();
    while (m.find()) {
      Object v = context.get(m.group(1));
      m.appendReplacement(sb, Matcher.quoteReplacement(v == null ? "" : String.valueOf(v)));
    }
    m.appendTail(sb);
    return sb.toString();
  }

  /** Sécurisation : restreint aux rôles → l'utilisateur doit en avoir un. */
  private boolean canRead(RecordTypeMetadataProfile profile, Set<String> userRoles) {
    String json = profile.getRestrictedToRoles();
    if (json == null || json.isBlank()) return true;
    Object parsed = this.parseJson(json);
    if (!(parsed instanceof List<?> list)) return true;
    if (list.isEmpty()) return true;
    for (Object role : list) {
      if (role != null && userRoles != null && userRoles.contains(String.valueOf(role))) {
        return true;
      }
    }
    return false;
  }

  private Object parseJson(String json) {
    if (json == null || json.isBlank()) return null;
    try {
      return this.mapper.readValue(json, Object.class);
    } catch (Exception e) {
      return null;
    }
  }
}
