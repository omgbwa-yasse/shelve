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

  @Column(name = "code_prefix", length = 191)
  private String codePrefix;

  @Column(name = "code_pattern", length = 191)
  private String codePattern;

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

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
