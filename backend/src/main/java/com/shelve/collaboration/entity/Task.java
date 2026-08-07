package com.shelve.collaboration.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import java.time.LocalDate;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "tasks")
public class Task {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "title")
  private String title;

  @Column(name = "description")
  private String description;

  @Column(name = "status")
  private String status = "todo";

  @Column(name = "priority")
  private String priority = "medium";

  @Column(name = "assigned_to")
  private Long assignedTo;

  @Column(name = "workflow_instance_id")
  private Long workflowInstanceId;

  @Column(name = "task_key")
  private String taskKey;

  @Column(name = "form_data")
  private String formData;

  @Column(name = "sequence_order")
  private Integer sequenceOrder;

  @Column(name = "parent_task_id")
  private Long parentTaskId;

  @Column(name = "taskable_type")
  private String taskableType;

  @Column(name = "taskable_id")
  private Long taskableId;

  @Column(name = "due_date")
  private Instant dueDate;

  @Column(name = "start_date")
  private LocalDate startDate;

  @Column(name = "percent_complete")
  private Integer percentComplete = 0;

  @Column(name = "estimated_hours")
  private String estimatedHours;

  @Column(name = "actual_hours")
  private String actualHours;

  @CreationTimestamp
  @Column(name = "created_by")
  private Long createdBy;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public Long getCreatedBy() {
    return this.createdBy;
  }

  public void setCreatedBy(Long v) {
    this.createdBy = v;
  }

  public Long getId() {
    return this.id;
  }

  public void setId(Long id) {
    this.id = id;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long v) {
    this.organisationId = v;
  }

  public String getTitle() {
    return this.title;
  }

  public void setTitle(String v) {
    this.title = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public String getPriority() {
    return this.priority;
  }

  public void setPriority(String v) {
    this.priority = v;
  }

  public Long getAssignedTo() {
    return this.assignedTo;
  }

  public void setAssignedTo(Long v) {
    this.assignedTo = v;
  }

  public Long getWorkflowInstanceId() {
    return this.workflowInstanceId;
  }

  public void setWorkflowInstanceId(Long v) {
    this.workflowInstanceId = v;
  }

  public String getTaskKey() {
    return this.taskKey;
  }

  public void setTaskKey(String v) {
    this.taskKey = v;
  }

  public String getFormData() {
    return this.formData;
  }

  public void setFormData(String v) {
    this.formData = v;
  }

  public Integer getSequenceOrder() {
    return this.sequenceOrder;
  }

  public void setSequenceOrder(Integer v) {
    this.sequenceOrder = v;
  }

  public Long getParentTaskId() {
    return this.parentTaskId;
  }

  public void setParentTaskId(Long v) {
    this.parentTaskId = v;
  }

  public String getTaskableType() {
    return this.taskableType;
  }

  public void setTaskableType(String v) {
    this.taskableType = v;
  }

  public Long getTaskableId() {
    return this.taskableId;
  }

  public void setTaskableId(Long v) {
    this.taskableId = v;
  }

  public Instant getDueDate() {
    return this.dueDate;
  }

  public void setDueDate(Instant v) {
    this.dueDate = v;
  }

  public LocalDate getStartDate() {
    return this.startDate;
  }

  public void setStartDate(LocalDate v) {
    this.startDate = v;
  }

  public Integer getPercentComplete() {
    return this.percentComplete;
  }

  public void setPercentComplete(Integer v) {
    this.percentComplete = v;
  }

  public String getEstimatedHours() {
    return this.estimatedHours;
  }

  public void setEstimatedHours(String v) {
    this.estimatedHours = v;
  }

  public String getActualHours() {
    return this.actualHours;
  }

  public void setActualHours(String v) {
    this.actualHours = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
