package com.shelve.ai.sandbox.service;

import com.shelve.ai.sandbox.config.SandboxProperties;
import com.shelve.exception.ApiException;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.List;
import java.util.Map;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;

/** Charge le catalogue des capacités du sandbox Python (`capabilities.json` partagé avec Laravel). */
@Service
public class SandboxCapabilities {
  private final SandboxProperties props;

  public SandboxCapabilities(SandboxProperties props) {
    this.props = props;
  }

  public Map<String, Object> manifest() {
    Path path = Paths.get(this.props.getCapabilities());
    if (!Files.isRegularFile(path)) {
      return Map.of(
          "runtime", "python3.12",
          "patterns", List.of("standard"),
          "capabilities", List.of());
    }
    try {
      String raw = Files.readString(path, StandardCharsets.UTF_8);
      return com.fasterxml.jackson.databind.json.JsonMapper.builder().build().readValue(raw, Map.class);
    } catch (IOException e) {
      throw new ApiException(HttpStatus.INTERNAL_SERVER_ERROR, "Catalogue de capacités illisible.");
    }
  }
}
