package com.shelve.opac.service;

import com.shelve.common.Json;
import com.shelve.opac.entity.OpacBlock;
import com.shelve.opac.entity.OpacPage;
import com.shelve.opac.entity.OpacPageVersion;
import com.shelve.opac.repository.OpacBlockRepository;
import com.shelve.organisation.repository.UserRepository;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.stereotype.Component;

/** Mappers JSON partagés du domaine OPAC (enveloppes Laravel identiques). */
@Component
public class OpacMapper {
  private final OpacJson json;
  private final OpacBlockRepository blocks;
  private final UserRepository users;

  public OpacMapper(OpacJson json, OpacBlockRepository blocks, UserRepository users) {
    this.json = json;
    this.blocks = blocks;
    this.users = users;
  }

  public Map<String, Object> page(OpacPage p, long versionCount) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", p.getId());
    map.put("slug", p.getSlug());
    map.put("title", p.getTitle());
    map.put("description", p.getDescription());
    map.put("status", p.getStatus());
    map.put("is_default", Boolean.TRUE.equals(p.getIsDefault()));
    map.put("versions_count", versionCount);
    map.put("created_at", Json.timestamp(p.getCreatedAt()));
    map.put("updated_at", Json.timestamp(p.getUpdatedAt()));
    return map;
  }

  public Map<String, Object> version(OpacPageVersion v, boolean withBlocks) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", v.getId());
    map.put("page_id", v.getOpacPageId());
    map.put("name", v.getName());
    map.put("status", v.getStatus());
    map.put("is_active", Boolean.TRUE.equals(v.getIsActive()));
    map.put("is_editable", v.isEditable());
    map.put("change_description", v.getChangeDescription());
    map.put("published_at", Json.timestamp(v.getPublishedAt()));
    map.put(
        "created_by",
        v.getCreatedBy() == null
            ? null
            : this.users
                .findById(v.getCreatedBy())
                .map(u -> u.getName())
                .orElse(String.valueOf(v.getCreatedBy())));
    map.put("created_at", Json.timestamp(v.getCreatedAt()));
    map.put("updated_at", Json.timestamp(v.getUpdatedAt()));
    if (withBlocks) {
      map.put("blocks", this.blocksOf(v.getId()));
    }
    return map;
  }

  public List<Map<String, Object>> blocksOf(Long versionId) {
    List<Map<String, Object>> list = new ArrayList<Map<String, Object>>();
    for (OpacBlock b : this.blocks.findAllByOpacPageVersionIdOrderByZoneAscOrderAsc(versionId)) {
      list.add(this.block(b));
    }
    return list;
  }

  public Map<String, Object> block(OpacBlock b) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", b.getId());
    map.put("zone", b.getZone());
    map.put("module_key", b.getModuleKey());
    Map<String, Object> module = OpacModuleRegistry.get(b.getModuleKey());
    if (module != null) {
      LinkedHashMap<String, Object> schema = new LinkedHashMap<String, Object>();
      schema.put("key", b.getModuleKey());
      schema.put("label", module.get("label"));
      schema.put("icon", module.get("icon"));
      schema.put("variants", module.get("variants"));
      schema.put("settings", module.get("settings"));
      map.put("module", schema);
    } else {
      map.put("module", null);
    }
    map.put("settings", this.json.readMap(b.getSettings()));
    map.put("responsive", this.json.readMap(b.getResponsive()));
    map.put("order", b.getOrder());
    return map;
  }
}
