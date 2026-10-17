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

/** Version d'une page OPAC (table `opac_page_versions`) — instantané des blocs. */
@Entity
@Table(name = "opac_page_versions")
public class OpacPageVersion {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "opac_page_id")
  private Long opacPageId;

  @Column(name = "name")
  private String name;

  @Column(name = "status")
  private String status = "draft";

  @Column(name = "is_active")
  private Boolean isActive = false;

  @Column(name = "change_description")
  private String changeDescription;

  @Column(name = "meta")
  private String meta;

  @Column(name = "created_by")
  private Long createdBy;

  @Column(name = "published_at")
  private Instant publishedAt;

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

  public Long getOpacPageId() {
    return this.opacPageId;
  }

  public void setOpacPageId(Long v) {
    this.opacPageId = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public Boolean getIsActive() {
    return this.isActive;
  }

  public void setIsActive(Boolean v) {
    this.isActive = v;
  }

  public String getChangeDescription() {
    return this.changeDescription;
  }

  public void setChangeDescription(String v) {
    this.changeDescription = v;
  }

  public String getMeta() {
    return this.meta;
  }

  public void setMeta(String v) {
    this.meta = v;
  }

  public Long getCreatedBy() {
    return this.createdBy;
  }

  public void setCreatedBy(Long v) {
    this.createdBy = v;
  }

  public Instant getPublishedAt() {
    return this.publishedAt;
  }

  public void setPublishedAt(Instant v) {
    this.publishedAt = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }

  public boolean isEditable() {
    return "draft".equals(this.status);
  }
}
