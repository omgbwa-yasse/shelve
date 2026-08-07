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
@Table(name = "ai_skills")
public class AiSkill {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "slug")
  private String slug;

  @Column(name = "name")
  private String name;

  @Column(name = "description")
  private String description;

  @Column(name = "version")
  private String version;

  @Column(name = "location")
  private String location = "custom";

  @Column(name = "folder")
  private String folder;

  @Column(name = "enabled")
  private Boolean enabled = true;

  @Column(name = "installed_by")
  private Long installedBy;

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

  public String getSlug() {
    return this.slug;
  }

  public void setSlug(String v) {
    this.slug = v;
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

  public String getVersion() {
    return this.version;
  }

  public void setVersion(String v) {
    this.version = v;
  }

  public String getLocation() {
    return this.location;
  }

  public void setLocation(String v) {
    this.location = v;
  }

  public String getFolder() {
    return this.folder;
  }

  public void setFolder(String v) {
    this.folder = v;
  }

  public Boolean getEnabled() {
    return this.enabled;
  }

  public void setEnabled(Boolean v) {
    this.enabled = v;
  }

  public Long getInstalledBy() {
    return this.installedBy;
  }

  public void setInstalledBy(Long v) {
    this.installedBy = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
