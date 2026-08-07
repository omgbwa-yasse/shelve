package com.shelve.ai.sandbox.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import java.time.LocalDateTime;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

/** Sandbox Python de l'assistant IA (D14) — table `ai_sandboxes` partagée avec Laravel. */
@Entity
@Table(name = "ai_sandboxes")
public class AiSandbox {
  public static final String PATTERN_STANDARD = "standard";
  public static final String ENGINE_LOCAL = "local";
  public static final String ENGINE_DOCKER = "docker";
  public static final String STATUS_CREATED = "created";
  public static final String STATUS_RUNNING = "running";
  public static final String STATUS_SUCCESS = "success";
  public static final String STATUS_ERROR = "error";
  public static final String STATUS_EXPIRED = "expired";

  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "user_id")
  private Long userId;

  @Column(name = "conversation_id")
  private Long conversationId;

  @Column(name = "name")
  private String name;

  @Column(name = "pattern")
  private String pattern = PATTERN_STANDARD;

  @Column(name = "engine")
  private String engine = ENGINE_LOCAL;

  @Column(name = "status")
  private String status = STATUS_CREATED;

  @Column(name = "folder")
  private String folder;

  @Column(name = "last_output")
  private String lastOutput;

  @Column(name = "expires_at")
  private LocalDateTime expiresAt;

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

  public Long getConversationId() {
    return this.conversationId;
  }

  public void setConversationId(Long v) {
    this.conversationId = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getPattern() {
    return this.pattern;
  }

  public void setPattern(String v) {
    this.pattern = v;
  }

  public String getEngine() {
    return this.engine;
  }

  public void setEngine(String v) {
    this.engine = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public String getFolder() {
    return this.folder;
  }

  public void setFolder(String v) {
    this.folder = v;
  }

  public String getLastOutput() {
    return this.lastOutput;
  }

  public void setLastOutput(String v) {
    this.lastOutput = v;
  }

  public LocalDateTime getExpiresAt() {
    return this.expiresAt;
  }

  public void setExpiresAt(LocalDateTime v) {
    this.expiresAt = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
