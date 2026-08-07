package com.shelve.slips.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.slips.entity.SlipRecordContainer;
import com.shelve.slips.entity.SlipRecordContainerId;
import com.shelve.slips.repository.SlipRecordContainerRepository;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/slips/{slipId}/records/{recordId}/containers"})
public class SlipRecordContainerController {
  private final SlipRecordContainerRepository repository;

  public SlipRecordContainerController(SlipRecordContainerRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(
      @PathVariable Long slipId, @PathVariable Long recordId, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_viewAny");
    List<Map<String, Object>> items =
        this.repository
            .findAll(
                (Specification<SlipRecordContainer>)
                    (root, q, cb) -> cb.equal(root.get("id").get("slipRecordId"), recordId))
            .stream()
            .map(this::mapper)
            .toList();
    return Json.of("data", items);
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(
      @PathVariable Long slipId,
      @PathVariable Long recordId,
      @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_create");
    Long containerId = SlipRecordContainerController.longOf(body.get("container_id"));
    if (containerId == null) {
      throw new ValidationException(
          Map.of("container_id", List.of("The container id field is required.")));
    }
    SlipRecordContainerId id = new SlipRecordContainerId(recordId, containerId);
    boolean created = !this.repository.existsById(id);
    boolean bl = created;
    if (created) {
      SlipRecordContainer pivot = new SlipRecordContainer();
      pivot.setId(id);
      pivot.setCreatorId(auth.user().getId());
      pivot.setDescription(SlipRecordContainerController.str(body.get("description")));
      this.repository.save(pivot);
    }
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) (created ? 201 : 200))
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/slips/" + slipId + "/records/" + recordId + "/containers"
                    }))
        .body(Json.of("data", this.mapper(this.resolve(recordId, containerId))));
  }

  @DeleteMapping(value = {"/{containerId}"})
  public ResponseEntity<Void> destroy(
      @PathVariable Long slipId, @PathVariable Long recordId, @PathVariable Long containerId) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_delete");
    this.resolve(recordId, containerId);
    this.repository.deleteById(new SlipRecordContainerId(recordId, containerId));
    return ResponseEntity.noContent().build();
  }

  private SlipRecordContainer resolve(Long recordId, Long containerId) {
    return (SlipRecordContainer)
        this.repository
            .findById(new SlipRecordContainerId(recordId, containerId))
            .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> mapper(SlipRecordContainer p) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("slip_record_id", p.getId() != null ? p.getId().getSlipRecordId() : null);
    map.put("container_id", p.getId() != null ? p.getId().getContainerId() : null);
    map.put("creator_id", p.getCreatorId());
    map.put("description", p.getDescription());
    map.put("created_at", Json.timestamp(p.getCreatedAt()));
    map.put("updated_at", Json.timestamp(p.getUpdatedAt()));
    return map;
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Long longOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return ((Number) value).longValue();
    } catch (ClassCastException e) {
      try {
        return Long.parseLong(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }
}
