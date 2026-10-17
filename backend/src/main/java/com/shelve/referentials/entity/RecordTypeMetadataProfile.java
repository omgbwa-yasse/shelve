package com.shelve.referentials.entity;

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
@Table(name = "record_type_metadata_profiles")
public class RecordTypeMetadataProfile {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "record_type_id", nullable = false)
  private Long recordTypeId;

  @Column(name = "metadata_definition_id", nullable = false)
  private Long metadataDefinitionId;

  @Column(nullable = false)
  private Boolean mandatory = false;

  @Column(nullable = false)
  private Boolean visible = true;

  @Column(nullable = false)
  private Boolean readonly = false;

  @Column(name = "default_value", columnDefinition = "text")
  private String defaultValue;

  @Column(name = "validation_rules", columnDefinition = "json")
  private String validationRules;

  @Column(name = "`group`", length = 100)
  private String group;

  @Column(name = "restricted_to_roles", columnDefinition = "json")
  private String restrictedToRoles;

  @Column(name = "sort_order", nullable = false)
  private Integer sortOrder = 0;

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

  public Long getId() { return this.id; }
  public void setId(Long id) { this.id = id; }
  public Long getRecordTypeId() { return this.recordTypeId; }
  public void setRecordTypeId(Long recordTypeId) { this.recordTypeId = recordTypeId; }
  public Long getMetadataDefinitionId() { return this.metadataDefinitionId; }
  public void setMetadataDefinitionId(Long metadataDefinitionId) { this.metadataDefinitionId = metadataDefinitionId; }
  public Boolean getMandatory() { return this.mandatory; }
  public void setMandatory(Boolean mandatory) { this.mandatory = mandatory; }
  public Boolean getVisible() { return this.visible; }
  public void setVisible(Boolean visible) { this.visible = visible; }
  public Boolean getReadonly() { return this.readonly; }
  public void setReadonly(Boolean readonly) { this.readonly = readonly; }
  public String getDefaultValue() { return this.defaultValue; }
  public void setDefaultValue(String defaultValue) { this.defaultValue = defaultValue; }
  public String getValidationRules() { return this.validationRules; }
  public void setValidationRules(String validationRules) { this.validationRules = validationRules; }
  public String getGroup() { return this.group; }
  public void setGroup(String group) { this.group = group; }
  public String getRestrictedToRoles() { return this.restrictedToRoles; }
  public void setRestrictedToRoles(String restrictedToRoles) { this.restrictedToRoles = restrictedToRoles; }
  public Integer getSortOrder() { return this.sortOrder; }
  public void setSortOrder(Integer sortOrder) { this.sortOrder = sortOrder; }
  public Long getCreatedBy() { return this.createdBy; }
  public void setCreatedBy(Long createdBy) { this.createdBy = createdBy; }
  public Long getUpdatedBy() { return this.updatedBy; }
  public void setUpdatedBy(Long updatedBy) { this.updatedBy = updatedBy; }
  public Instant getCreatedAt() { return this.createdAt; }
  public Instant getUpdatedAt() { return this.updatedAt; }
}
