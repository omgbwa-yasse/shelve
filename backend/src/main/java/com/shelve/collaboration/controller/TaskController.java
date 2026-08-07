package com.shelve.collaboration.controller;

import com.shelve.collaboration.entity.Task;
import com.shelve.collaboration.repository.TaskRepository;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
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
@RequestMapping(value = {"/api/v1/tasks"})
public class TaskController extends GenericCrudController<Task> {
  private final TaskRepository repo;

  public TaskController(TaskRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Task> entityClass() {
    return Task.class;
  }

  @Override
  protected JpaSpecificationExecutor<Task> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "task";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "organisation_id",
        "title",
        "description",
        "status",
        "priority",
        "assigned_to",
        "workflow_instance_id",
        "task_key",
        "form_data",
        "sequence_order",
        "parent_task_id",
        "taskable_type",
        "taskable_id",
        "due_date",
        "start_date",
        "percent_complete");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Task newEntity() {
    return new Task();
  }

  @Override
  protected String location(Task e) {
    return "/api/v1/tasks/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (TaskController.str(body.get("title")) == null) {
      v.add("title", "The title field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Task e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setCreatedBy(auth.user().getId());
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(TaskController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("title")) {
      e.setTitle(TaskController.str(body.get("title")));
    }
    if (body.containsKey("description")) {
      e.setDescription(TaskController.str(body.get("description")));
    }
    if (body.containsKey("status")) {
      e.setStatus(TaskController.str(body.get("status")));
    }
    if (body.containsKey("priority")) {
      e.setPriority(TaskController.str(body.get("priority")));
    }
    if (body.containsKey("assigned_to")) {
      e.setAssignedTo(TaskController.longOf(body.get("assigned_to")));
    }
    if (body.containsKey("workflow_instance_id")) {
      e.setWorkflowInstanceId(TaskController.longOf(body.get("workflow_instance_id")));
    }
    if (body.containsKey("task_key")) {
      e.setTaskKey(TaskController.str(body.get("task_key")));
    }
    if (body.containsKey("form_data")) {
      e.setFormData(TaskController.str(body.get("form_data")));
    }
    if (body.containsKey("sequence_order")) {
      e.setSequenceOrder(TaskController.intOf(body.get("sequence_order")));
    }
    if (body.containsKey("parent_task_id")) {
      e.setParentTaskId(TaskController.longOf(body.get("parent_task_id")));
    }
    if (body.containsKey("taskable_type")) {
      e.setTaskableType(TaskController.str(body.get("taskable_type")));
    }
    if (body.containsKey("taskable_id")) {
      e.setTaskableId(TaskController.longOf(body.get("taskable_id")));
    }
    if (body.containsKey("due_date")) {
      e.setDueDate(TaskController.instantOf(body.get("due_date")));
    }
    if (body.containsKey("start_date")) {
      e.setStartDate(TaskController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("percent_complete")) {
      e.setPercentComplete(TaskController.intOf(body.get("percent_complete")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Task e) {
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(TaskController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("title")) {
      e.setTitle(TaskController.str(body.get("title")));
    }
    if (body.containsKey("description")) {
      e.setDescription(TaskController.str(body.get("description")));
    }
    if (body.containsKey("status")) {
      e.setStatus(TaskController.str(body.get("status")));
    }
    if (body.containsKey("priority")) {
      e.setPriority(TaskController.str(body.get("priority")));
    }
    if (body.containsKey("assigned_to")) {
      e.setAssignedTo(TaskController.longOf(body.get("assigned_to")));
    }
    if (body.containsKey("workflow_instance_id")) {
      e.setWorkflowInstanceId(TaskController.longOf(body.get("workflow_instance_id")));
    }
    if (body.containsKey("task_key")) {
      e.setTaskKey(TaskController.str(body.get("task_key")));
    }
    if (body.containsKey("form_data")) {
      e.setFormData(TaskController.str(body.get("form_data")));
    }
    if (body.containsKey("sequence_order")) {
      e.setSequenceOrder(TaskController.intOf(body.get("sequence_order")));
    }
    if (body.containsKey("parent_task_id")) {
      e.setParentTaskId(TaskController.longOf(body.get("parent_task_id")));
    }
    if (body.containsKey("taskable_type")) {
      e.setTaskableType(TaskController.str(body.get("taskable_type")));
    }
    if (body.containsKey("taskable_id")) {
      e.setTaskableId(TaskController.longOf(body.get("taskable_id")));
    }
    if (body.containsKey("due_date")) {
      e.setDueDate(TaskController.instantOf(body.get("due_date")));
    }
    if (body.containsKey("start_date")) {
      e.setStartDate(TaskController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("percent_complete")) {
      e.setPercentComplete(TaskController.intOf(body.get("percent_complete")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Task e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("title", e.getTitle());
    map.put("description", e.getDescription());
    map.put("status", e.getStatus());
    map.put("priority", e.getPriority());
    map.put("assigned_to", e.getAssignedTo());
    map.put("workflow_instance_id", e.getWorkflowInstanceId());
    map.put("task_key", e.getTaskKey());
    map.put("form_data", e.getFormData());
    map.put("sequence_order", e.getSequenceOrder());
    map.put("parent_task_id", e.getParentTaskId());
    map.put("taskable_type", e.getTaskableType());
    map.put("taskable_id", e.getTaskableId());
    map.put("due_date", Json.timestamp(e.getDueDate()));
    map.put("start_date", e.getStartDate() != null ? e.getStartDate().toString() : null);
    map.put("percent_complete", e.getPercentComplete());
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
