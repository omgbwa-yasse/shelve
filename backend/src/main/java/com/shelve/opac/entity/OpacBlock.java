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

/** Bloc d'une version de page OPAC (table `opac_blocks`). */
@Entity
@Table(name = "opac_blocks")
public class OpacBlock {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "opac_page_version_id")
  private Long opacPageVersionId;

  @Column(name = "zone")
  private String zone;

  @Column(name = "module_key")
  private String moduleKey;

  @Column(name = "settings")
  private String settings;

  @Column(name = "responsive")
  private String responsive;

  @Column(name = "`order`")
  private Integer order = 0;

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

  public Long getOpacPageVersionId() {
    return this.opacPageVersionId;
  }

  public void setOpacPageVersionId(Long v) {
    this.opacPageVersionId = v;
  }

  public String getZone() {
    return this.zone;
  }

  public void setZone(String v) {
    this.zone = v;
  }

  public String getModuleKey() {
    return this.moduleKey;
  }

  public void setModuleKey(String v) {
    this.moduleKey = v;
  }

  public String getSettings() {
    return this.settings;
  }

  public void setSettings(String v) {
    this.settings = v;
  }

  public String getResponsive() {
    return this.responsive;
  }

  public void setResponsive(String v) {
    this.responsive = v;
  }

  public Integer getOrder() {
    return this.order;
  }

  public void setOrder(Integer v) {
    this.order = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
