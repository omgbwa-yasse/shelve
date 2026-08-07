package com.shelve.workflow.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "workflow_instances")
public class WorkflowInstance {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "definition_id")
  private Long definitionId;

  @Column(name = "name")
  private String name;

  @Column(name = "status")
  private String status;

  @Column(name = "current_state")
  private String currentState;

  @Column(name = "started_by")
  private Long startedBy;

  @Column(name = "started_at")
  private Instant startedAt;

  @Column(name = "updated_by")
  private Long updatedBy;

  @Column(name = "completed_by")
  private Long completedBy;

  @Column(name = "completed_at")
  private Instant completedAt;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

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

  public Long getDefinitionId() {
    return this.definitionId;
  }

  public void setDefinitionId(Long v) {
    this.definitionId = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public String getCurrentState() {
    return this.currentState;
  }

  public void setCurrentState(String v) {
    this.currentState = v;
  }

  public Long getStartedBy() {
    return this.startedBy;
  }

  public void setStartedBy(Long v) {
    this.startedBy = v;
  }

  public Instant getStartedAt() {
    return this.startedAt;
  }

  public void setStartedAt(Instant v) {
    this.startedAt = v;
  }

  public Long getUpdatedBy() {
    return this.updatedBy;
  }

  public void setUpdatedBy(Long v) {
    this.updatedBy = v;
  }

  public Long getCompletedBy() {
    return this.completedBy;
  }

  public void setCompletedBy(Long v) {
    this.completedBy = v;
  }

  public Instant getCompletedAt() {
    return this.completedAt;
  }

  public void setCompletedAt(Instant v) {
    this.completedAt = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
