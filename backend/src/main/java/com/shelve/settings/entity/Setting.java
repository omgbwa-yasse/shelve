package com.shelve.settings.entity;

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
@Table(name = "settings")
public class Setting {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "category_id", nullable = false)
  private Long categoryId;

  @Column(nullable = false, unique = true, length = 100)
  private String name;

  @Column(nullable = false, length = 20)
  private String type = "string";

  @Column(name = "default_value", nullable = false, columnDefinition = "json")
  private String defaultValue;

  @Column(nullable = false, columnDefinition = "text")
  private String description;

  @Column(name = "is_system", nullable = false)
  private Boolean isSystem = false;

  @Column(columnDefinition = "json")
  private String constraints;

  @Column(name = "user_id")
  private Long userId;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(columnDefinition = "json")
  private String value;

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

  public Long getCategoryId() {
    return this.categoryId;
  }

  public void setCategoryId(Long v) {
    this.categoryId = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getType() {
    return this.type;
  }

  public void setType(String v) {
    this.type = v;
  }

  public String getDefaultValue() {
    return this.defaultValue;
  }

  public void setDefaultValue(String v) {
    this.defaultValue = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
  }

  public Boolean getIsSystem() {
    return this.isSystem;
  }

  public void setIsSystem(Boolean v) {
    this.isSystem = v;
  }

  public String getConstraints() {
    return this.constraints;
  }

  public void setConstraints(String v) {
    this.constraints = v;
  }

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long v) {
    this.userId = v;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long v) {
    this.organisationId = v;
  }

  public String getValue() {
    return this.value;
  }

  public void setValue(String v) {
    this.value = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
