package com.shelve.opac.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

/** Page OPAC construite avec le constructeur (table `opac_pages`). */
@Entity
@Table(name = "opac_pages")
public class OpacPage {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "slug")
  private String slug;

  @Column(name = "title")
  private String title;

  @Column(name = "description")
  private String description;

  @Column(name = "status")
  private String status = "draft";

  @Column(name = "is_default")
  private Boolean isDefault = false;

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

  public String getSlug() {
    return this.slug;
  }

  public void setSlug(String v) {
    this.slug = v;
  }

  public String getTitle() {
    return this.title;
  }

  public void setTitle(String v) {
    this.title = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public Boolean getIsDefault() {
    return this.isDefault;
  }

  public void setIsDefault(Boolean v) {
    this.isDefault = v;
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
