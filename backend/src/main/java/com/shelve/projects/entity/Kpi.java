package com.shelve.projects.entity;

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
@Table(name = "kpis")
public class Kpi {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "code")
  private String code;

  @Column(name = "name")
  private String name;

  @Column(name = "description")
  private String description;

  @Column(name = "unit")
  private String unit;

  @Column(name = "target_value")
  private String targetValue;

  @Column(name = "direction")
  private String direction = "higher_is_better";

  @Column(name = "frequency")
  private String frequency = "monthly";

  @Column(name = "task_id")
  private Long taskId;

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

  public String getCode() {
    return this.code;
  }

  public void setCode(String v) {
    this.code = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
  }

  public String getUnit() {
    return this.unit;
  }

  public void setUnit(String v) {
    this.unit = v;
  }

  public String getTargetValue() {
    return this.targetValue;
  }

  public void setTargetValue(String v) {
    this.targetValue = v;
  }

  public String getDirection() {
    return this.direction;
  }

  public void setDirection(String v) {
    this.direction = v;
  }

  public String getFrequency() {
    return this.frequency;
  }

  public void setFrequency(String v) {
    this.frequency = v;
  }

  public Long getTaskId() {
    return this.taskId;
  }

  public void setTaskId(Long v) {
    this.taskId = v;
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
