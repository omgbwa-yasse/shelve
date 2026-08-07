package com.shelve.organisation.entity;

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
@Table(name = "personal_access_tokens")
public class PersonalAccessToken {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "tokenable_type", nullable = false, length = 191)
  private String tokenableType;

  @Column(name = "tokenable_id", nullable = false)
  private Long tokenableId;

  @Column(nullable = false, length = 191)
  private String name;

  @Column(nullable = false, unique = true, length = 64)
  private String token;

  @Column(columnDefinition = "text")
  private String abilities;

  @Column(name = "last_used_at")
  private Instant lastUsedAt;

  @Column(name = "expires_at")
  private Instant expiresAt;

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

  public String getTokenableType() {
    return this.tokenableType;
  }

  public void setTokenableType(String tokenableType) {
    this.tokenableType = tokenableType;
  }

  public Long getTokenableId() {
    return this.tokenableId;
  }

  public void setTokenableId(Long tokenableId) {
    this.tokenableId = tokenableId;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String name) {
    this.name = name;
  }

  public String getToken() {
    return this.token;
  }

  public void setToken(String token) {
    this.token = token;
  }

  public String getAbilities() {
    return this.abilities;
  }

  public void setAbilities(String abilities) {
    this.abilities = abilities;
  }

  public Instant getLastUsedAt() {
    return this.lastUsedAt;
  }

  public void setLastUsedAt(Instant lastUsedAt) {
    this.lastUsedAt = lastUsedAt;
  }

  public Instant getExpiresAt() {
    return this.expiresAt;
  }

  public void setExpiresAt(Instant expiresAt) {
    this.expiresAt = expiresAt;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
