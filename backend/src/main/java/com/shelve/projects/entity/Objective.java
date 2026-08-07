package com.shelve.projects.entity;

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
@Table(name = "objectives")
public class Objective {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "project_id")
  private Long projectId;

  @Column(name = "task_id")
  private Long taskId;

  @Column(name = "title")
  private String title;

  @Column(name = "description")
  private String description;

  @Column(name = "period_start")
  private LocalDate periodStart;

  @Column(name = "period_end")
  private LocalDate periodEnd;

  @Column(name = "status")
  private String status = "on_track";

  @Column(name = "attachable_type")
  private String attachableType;

  @Column(name = "attachable_id")
  private Long attachableId;

  @Column(name = "owner_id")
  private Long ownerId;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "created_by")
  private Long createdBy;

  @Column(name = "updated_by")
  private Long updatedBy;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public String getAttachableType() {
    return this.attachableType;
  }

  public void setAttachableType(String v) {
    this.attachableType = v;
  }

  public Long getAttachableId() {
    return this.attachableId;
  }

  public void setAttachableId(Long v) {
    this.attachableId = v;
  }

  public Long getId() {
    return this.id;
  }

  public void setId(Long id) {
    this.id = id;
  }

  public Long getProjectId() {
    return this.projectId;
  }

  public void setProjectId(Long v) {
    this.projectId = v;
  }

  public Long getTaskId() {
    return this.taskId;
  }

  public void setTaskId(Long v) {
    this.taskId = v;
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

  public LocalDate getPeriodStart() {
    return this.periodStart;
  }

  public void setPeriodStart(LocalDate v) {
    this.periodStart = v;
  }

  public LocalDate getPeriodEnd() {
    return this.periodEnd;
  }

  public void setPeriodEnd(LocalDate v) {
    this.periodEnd = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public Long getOwnerId() {
    return this.ownerId;
  }

  public void setOwnerId(Long v) {
    this.ownerId = v;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long v) {
    this.organisationId = v;
  }

  public Long getCreatedBy() {
    return this.createdBy;
  }

  public void setCreatedBy(Long v) {
    this.createdBy = v;
  }

  public Long getUpdatedBy() {
    return this.updatedBy;
  }

  public void setUpdatedBy(Long v) {
    this.updatedBy = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
