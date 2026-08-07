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
@Table(name = "ai_conversations")
public class AiConversation {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "user_id")
  private Long userId;

  @Column(name = "title")
  private String title;

  @Column(name = "context")
  private String context;

  @Column(name = "archived_at")
  private Instant archivedAt;

  @Column(name = "mode")
  private String mode = "manuel";

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

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long v) {
    this.userId = v;
  }

  public String getTitle() {
    return this.title;
  }

  public void setTitle(String v) {
    this.title = v;
  }

  public String getContext() {
    return this.context;
  }

  public void setContext(String v) {
    this.context = v;
  }

  public Instant getArchivedAt() {
    return this.archivedAt;
  }

  public void setArchivedAt(Instant v) {
    this.archivedAt = v;
  }

  public String getMode() {
    return this.mode;
  }

  public void setMode(String v) {
    this.mode = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
