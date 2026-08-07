package com.shelve.mails.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.mails.entity.BatchTransaction;
import com.shelve.mails.repository.BatchTransactionRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/batch-transactions"})
public class BatchTransactionController extends GenericCrudController<BatchTransaction> {
  private final BatchTransactionRepository repo;

  public BatchTransactionController(BatchTransactionRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<BatchTransaction> entityClass() {
    return BatchTransaction.class;
  }

  @Override
  protected JpaSpecificationExecutor<BatchTransaction> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "batch_transaction";
  }

  @Override
  protected List<String> filterable() {
    return List.of("batch_id", "organisation_send_id", "organisation_received_id");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected BatchTransaction newEntity() {
    return new BatchTransaction();
  }

  @Override
  protected String location(BatchTransaction e) {
    return "/api/v1/batch-transactions/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (BatchTransactionController.str(body.get("batch_id")) == null) {
      v.add("batch_id", "The batch id field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, BatchTransaction e) {
    if (body.containsKey("batch_id")) {
      e.setBatchId(BatchTransactionController.intOf(body.get("batch_id")));
    }
    if (body.containsKey("organisation_send_id")) {
      e.setOrganisationSendId(BatchTransactionController.longOf(body.get("organisation_send_id")));
    }
    if (body.containsKey("organisation_received_id")) {
      e.setOrganisationReceivedId(
          BatchTransactionController.longOf(body.get("organisation_received_id")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, BatchTransaction e) {
    if (body.containsKey("batch_id")) {
      e.setBatchId(BatchTransactionController.intOf(body.get("batch_id")));
    }
    if (body.containsKey("organisation_send_id")) {
      e.setOrganisationSendId(BatchTransactionController.longOf(body.get("organisation_send_id")));
    }
    if (body.containsKey("organisation_received_id")) {
      e.setOrganisationReceivedId(
          BatchTransactionController.longOf(body.get("organisation_received_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(BatchTransaction e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("batch_id", e.getBatchId());
    map.put("organisation_send_id", e.getOrganisationSendId());
    map.put("organisation_received_id", e.getOrganisationReceivedId());
    map.put("created_at", Json.timestamp(e.getCreatedAt()));
    map.put("updated_at", Json.timestamp(e.getUpdatedAt()));
    return map;
  }

  @Override
  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    return super.index(request);
  }

  @Override
  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    return super.show(id);
  }

  @Override
  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    return super.store(body);
  }

  @Override
  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    return super.update(id, body);
  }

  @Override
  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    return super.destroy(id);
  }
}
