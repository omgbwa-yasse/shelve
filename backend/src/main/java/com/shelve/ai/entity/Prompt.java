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
@Table(name = "prompts")
public class Prompt {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "title")
  private String title;

  @Column(name = "content")
  private String content;

  @Column(name = "is_system")
  private Boolean isSystem = false;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "user_id")
  private Long userId;

  @Column(name = "prompt_category_id")
  private Long promptCategoryId;

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

  public String getTitle() {
    return this.title;
  }

  public void setTitle(String v) {
    this.title = v;
  }

  public String getContent() {
    return this.content;
  }

  public void setContent(String v) {
    this.content = v;
  }

  public Boolean getIsSystem() {
    return this.isSystem;
  }

  public void setIsSystem(Boolean v) {
    this.isSystem = v;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long v) {
    this.organisationId = v;
  }

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long v) {
    this.userId = v;
  }

  public Long getPromptCategoryId() {
    return this.promptCategoryId;
  }

  public void setPromptCategoryId(Long v) {
    this.promptCategoryId = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
