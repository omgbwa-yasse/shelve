package com.shelve.collaboration.entity;

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
@Table(name = "workplace_templates")
public class WorkplaceTemplate {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "code")
  private String code;

  @Column(name = "name")
  private String name;

  @Column(name = "description")
  private String description;

  @Column(name = "icon")
  private String icon;

  @Column(name = "category")
  private String category;

  @Column(name = "default_settings")
  private String defaultSettings;

  @Column(name = "default_structure")
  private String defaultStructure;

  @Column(name = "default_permissions")
  private String defaultPermissions;

  @Column(name = "is_active")
  private Boolean isActive;

  @Column(name = "is_system")
  private Boolean isSystem;

  @Column(name = "usage_count")
  private Integer usageCount;

  @Column(name = "display_order")
  private Integer displayOrder;

  @Column(name = "created_by")
  private Long createdBy;

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

  public String getIcon() {
    return this.icon;
  }

  public void setIcon(String v) {
    this.icon = v;
  }

  public String getCategory() {
    return this.category;
  }

  public void setCategory(String v) {
    this.category = v;
  }

  public String getDefaultSettings() {
    return this.defaultSettings;
  }

  public void setDefaultSettings(String v) {
    this.defaultSettings = v;
  }

  public String getDefaultStructure() {
    return this.defaultStructure;
  }

  public void setDefaultStructure(String v) {
    this.defaultStructure = v;
  }

  public String getDefaultPermissions() {
    return this.defaultPermissions;
  }

  public void setDefaultPermissions(String v) {
    this.defaultPermissions = v;
  }

  public Boolean getIsActive() {
    return this.isActive;
  }

  public void setIsActive(Boolean v) {
    this.isActive = v;
  }

  public Boolean getIsSystem() {
    return this.isSystem;
  }

  public void setIsSystem(Boolean v) {
    this.isSystem = v;
  }

  public Integer getUsageCount() {
    return this.usageCount;
  }

  public void setUsageCount(Integer v) {
    this.usageCount = v;
  }

  public Integer getDisplayOrder() {
    return this.displayOrder;
  }

  public void setDisplayOrder(Integer v) {
    this.displayOrder = v;
  }

  public Long getCreatedBy() {
    return this.createdBy;
  }

  public void setCreatedBy(Long v) {
    this.createdBy = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
