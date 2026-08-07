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
@Table(name = "workflow_definitions")
public class WorkflowDefinition {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "name")
  private String name;

  @Column(name = "description")
  private String description;

  @Column(name = "bpmn_xml")
  private String bpmnXml;

  @Column(name = "version")
  private Integer version = 1;

  @Column(name = "status")
  private String status;

  @Column(name = "visibility")
  private String visibility = "public";

  @Column(name = "allowed_user_ids")
  private String allowedUserIds;

  @Column(name = "allowed_role_ids")
  private String allowedRoleIds;

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

  public String getBpmnXml() {
    return this.bpmnXml;
  }

  public void setBpmnXml(String v) {
    this.bpmnXml = v;
  }

  public Integer getVersion() {
    return this.version;
  }

  public void setVersion(Integer v) {
    this.version = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public String getVisibility() {
    return this.visibility;
  }

  public void setVisibility(String v) {
    this.visibility = v;
  }

  public String getAllowedUserIds() {
    return this.allowedUserIds;
  }

  public void setAllowedUserIds(String v) {
    this.allowedUserIds = v;
  }

  public String getAllowedRoleIds() {
    return this.allowedRoleIds;
  }

  public void setAllowedRoleIds(String v) {
    this.allowedRoleIds = v;
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
