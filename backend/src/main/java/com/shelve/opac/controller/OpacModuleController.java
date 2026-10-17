package com.shelve.opac.controller;

import com.shelve.common.Json;
import com.shelve.opac.service.OpacModuleRegistry;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/** Catalogue des modules OPAC — servi à l'éditeur (back-office). */
@RestController
@RequestMapping("/api/v1/opac/modules")
public class OpacModuleController {

  /** GET /api/v1/opac/modules */
  @GetMapping
  public Map<String, Object> index() {
    List<Map<String, Object>> modules = new ArrayList<Map<String, Object>>();
    for (String key : OpacModuleRegistry.all().keySet()) {
      modules.add(OpacModuleRegistry.moduleSchema(key));
    }

    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("zones", OpacModuleRegistry.zones());
    data.put("breakpoints", OpacModuleRegistry.breakpoints());
    data.put("modules", modules);
    return Json.of("data", data);
  }
}
