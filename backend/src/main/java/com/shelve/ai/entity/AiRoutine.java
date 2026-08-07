package com.shelve.ai.entity;

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
@Table(name = "ai_routines")
public class AiRoutine {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "created_by")
  private Long createdBy;

  @Column(name = "name")
  private String name;

  @Column(name = "description")
  private String description;

  @Column(name = "prompt_id")
  private Long promptId;

  @Column(name = "skill_id")
  private Long skillId;

  @Column(name = "schedule_type")
  private String scheduleType = "once";

  @Column(name = "is_enabled")
  private Boolean isEnabled = true;

  @Column(name = "last_status")
  private String lastStatus;

  @Column(name = "last_output")
  private String lastOutput;

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

  public Long getCreatedBy() {
    return this.createdBy;
  }

  public void setCreatedBy(Long v) {
    this.createdBy = v;
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

  public Long getPromptId() {
    return this.promptId;
  }

  public void setPromptId(Long v) {
    this.promptId = v;
  }

  public Long getSkillId() {
    return this.skillId;
  }

  public void setSkillId(Long v) {
    this.skillId = v;
  }

  public String getScheduleType() {
    return this.scheduleType;
  }

  public void setScheduleType(String v) {
    this.scheduleType = v;
  }

  public Boolean getIsEnabled() {
    return this.isEnabled;
  }

  public void setIsEnabled(Boolean v) {
    this.isEnabled = v;
  }

  public String getLastStatus() {
    return this.lastStatus;
  }

  public void setLastStatus(String v) {
    this.lastStatus = v;
  }

  public String getLastOutput() {
    return this.lastOutput;
  }

  public void setLastOutput(String v) {
    this.lastOutput = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
