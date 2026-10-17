package com.shelve.referentials.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.SQLDelete;
import org.hibernate.annotations.SQLRestriction;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "metadata_definitions")
@SQLRestriction(value = "deleted_at is null")
@SQLDelete(sql = "update metadata_definitions set deleted_at = now(), updated_at = now() where id = ?")
public class MetadataDefinition {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, length = 100)
  private String name;

  @Column(nullable = false, unique = true, length = 50)
  private String code;

  @Column(columnDefinition = "text")
  private String description;

  @Column(name = "data_type", nullable = false, length = 20)
  private String dataType = "text";

  @Column(name = "validation_rules", columnDefinition = "json")
  private String validationRules;

  @Column(columnDefinition = "json")
  private String options;

  @Column(name = "reference_list_id")
  private Long referenceListId;

  @Column(nullable = false)
  private Boolean searchable = true;

  @Column(nullable = false)
  private Boolean sortable = false;

  @Column(nullable = false)
  private Boolean highlightable = false;

  @Column(nullable = false)
  private Boolean autocomplete = false;

  @Column(name = "`unique`", nullable = false)
  private Boolean unique = false;
  @Column(name = "input_mask", length = 100)
  private String inputMask;

  @Column(name = "max_length")
  private Integer maxLength;

  @Column(name = "copy_source_type", length = 30)
  private String copySourceType;

  @Column(name = "copy_source_field", length = 100)
  private String copySourceField;

  @Column(name = "computed_template", length = 255)
  private String computedTemplate;

  @Column(nullable = false)
  private Boolean active = true;

  @Column(name = "is_system", nullable = false)
  private Boolean isSystem = false;

  @Column(name = "sort_order", nullable = false)
  private Integer sortOrder = 0;

  @Column(name = "created_by", nullable = false)
  private Long createdBy;

  @Column(name = "updated_by")
  private Long updatedBy;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  @Column(name = "deleted_at", insertable = false, updatable = false)
  private Instant deletedAt;

  public Long getId() { return this.id; }
  public void setId(Long id) { this.id = id; }
  public String getName() { return this.name; }
  public void setName(String name) { this.name = name; }
  public String getCode() { return this.code; }
  public void setCode(String code) { this.code = code; }
  public String getDescription() { return this.description; }
  public void setDescription(String description) { this.description = description; }
  public String getDataType() { return this.dataType; }
  public void setDataType(String dataType) { this.dataType = dataType; }
  public String getValidationRules() { return this.validationRules; }
  public void setValidationRules(String validationRules) { this.validationRules = validationRules; }
  public String getOptions() { return this.options; }
  public void setOptions(String options) { this.options = options; }
  public Long getReferenceListId() { return this.referenceListId; }
  public void setReferenceListId(Long referenceListId) { this.referenceListId = referenceListId; }
  public Boolean getSearchable() { return this.searchable; }
  public void setSearchable(Boolean searchable) { this.searchable = searchable; }
  public Boolean getSortable() { return this.sortable; }
  public void setSortable(Boolean sortable) { this.sortable = sortable; }
  public Boolean getHighlightable() { return this.highlightable; }
  public void setHighlightable(Boolean highlightable) { this.highlightable = highlightable; }
  public Boolean getAutocomplete() { return this.autocomplete; }
  public void setAutocomplete(Boolean autocomplete) { this.autocomplete = autocomplete; }
  public Boolean getUnique() { return this.unique; }
  public void setUnique(Boolean unique) { this.unique = unique; }
  public String getInputMask() { return this.inputMask; }
  public void setInputMask(String inputMask) { this.inputMask = inputMask; }
  public Integer getMaxLength() { return this.maxLength; }
  public void setMaxLength(Integer maxLength) { this.maxLength = maxLength; }
  public String getCopySourceType() { return this.copySourceType; }
  public void setCopySourceType(String copySourceType) { this.copySourceType = copySourceType; }
  public String getCopySourceField() { return this.copySourceField; }
  public void setCopySourceField(String copySourceField) { this.copySourceField = copySourceField; }
  public String getComputedTemplate() { return this.computedTemplate; }
  public void setComputedTemplate(String computedTemplate) { this.computedTemplate = computedTemplate; }
  public Boolean getActive() { return this.active; }
  public void setActive(Boolean active) { this.active = active; }
  public Boolean getIsSystem() { return this.isSystem; }
  public void setIsSystem(Boolean isSystem) { this.isSystem = isSystem; }
  public Integer getSortOrder() { return this.sortOrder; }
  public void setSortOrder(Integer sortOrder) { this.sortOrder = sortOrder; }
  public Long getCreatedBy() { return this.createdBy; }
  public void setCreatedBy(Long createdBy) { this.createdBy = createdBy; }
  public Long getUpdatedBy() { return this.updatedBy; }
  public void setUpdatedBy(Long updatedBy) { this.updatedBy = updatedBy; }
  public Instant getCreatedAt() { return this.createdAt; }
  public Instant getUpdatedAt() { return this.updatedAt; }
  public Instant getDeletedAt() { return this.deletedAt; }
}
