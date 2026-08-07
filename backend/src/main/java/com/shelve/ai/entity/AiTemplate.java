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
@Table(name = "ai_templates")
public class AiTemplate {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "name")
  private String name;

  @Column(name = "category")
  private String category;

  @Column(name = "file_name")
  private String fileName;

  @Column(name = "file_path")
  private String filePath;

  @Column(name = "mime_type")
  private String mimeType;

  @Column(name = "size")
  private Long size = 0L;

  @Column(name = "description")
  private String description;

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

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getCategory() {
    return this.category;
  }

  public void setCategory(String v) {
    this.category = v;
  }

  public String getFileName() {
    return this.fileName;
  }

  public void setFileName(String v) {
    this.fileName = v;
  }

  public String getFilePath() {
    return this.filePath;
  }

  public void setFilePath(String v) {
    this.filePath = v;
  }

  public String getMimeType() {
    return this.mimeType;
  }

  public void setMimeType(String v) {
    this.mimeType = v;
  }

  public Long getSize() {
    return this.size;
  }

  public void setSize(Long v) {
    this.size = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
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
