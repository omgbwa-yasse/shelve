package com.shelve.records.entity;

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
@Table(name = "record_types")
public class RecordType {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 50)
  private String code;

  @Column(nullable = false, length = 150)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(name = "is_container", nullable = false)
  private Boolean isContainer = false;

  @Column(name = "reference_list_id")
  private Long referenceListId;

  @Column(name = "code_prefix", length = 191)
  private String codePrefix;

  @Column(name = "code_pattern", length = 191)
  private String codePattern;

  @Column(name = "is_active", nullable = false)
  private Boolean isActive = true;

  @Column(name = "display_order", nullable = false)
  private Integer displayOrder = 0;

  @Column(name = "requires_versioning", nullable = false)
  private Boolean requiresVersioning = false;

  @Column(name = "requires_approval", nullable = false)
  private Boolean requiresApproval = false;

  @Column(name = "default_access_level", nullable = false, length = 20)
  private String defaultAccessLevel = "internal";

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

  public boolean isContainer() {
    return this.isContainer != null && this.isContainer != false;
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

  public Boolean getIsContainer() {
    return this.isContainer;
  }

  public void setIsContainer(Boolean v) {
    this.isContainer = v;
  }

  public Long getReferenceListId() {
    return this.referenceListId;
  }

  public void setReferenceListId(Long v) {
    this.referenceListId = v;
  }

  public String getCodePrefix() {
    return this.codePrefix;
  }

  public void setCodePrefix(String v) {
    this.codePrefix = v;
  }

  public String getCodePattern() {
    return this.codePattern;
  }

  public void setCodePattern(String v) {
    this.codePattern = v;
  }

  public Boolean getIsActive() {
    return this.isActive;
  }

  public void setIsActive(Boolean v) {
    this.isActive = v;
  }

  public Integer getDisplayOrder() {
    return this.displayOrder;
  }

  public void setDisplayOrder(Integer v) {
    this.displayOrder = v;
  }

  public Boolean getRequiresVersioning() {
    return this.requiresVersioning;
  }

  public void setRequiresVersioning(Boolean v) {
    this.requiresVersioning = v;
  }

  public Boolean getRequiresApproval() {
    return this.requiresApproval;
  }

  public void setRequiresApproval(Boolean v) {
    this.requiresApproval = v;
  }

  public String getDefaultAccessLevel() {
    return this.defaultAccessLevel;
  }

  public void setDefaultAccessLevel(String v) {
    this.defaultAccessLevel = v;
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
