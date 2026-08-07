package com.shelve.thesaurus.entity;

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
@Table(name = "thesaurus_concepts")
public class ThesaurusConcept {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "scheme_id")
  private Long schemeId;

  @Column(name = "uri")
  private String uri;

  @Column(name = "notation")
  private String notation;

  @Column(name = "status")
  private Integer status;

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

  public Long getSchemeId() {
    return this.schemeId;
  }

  public void setSchemeId(Long v) {
    this.schemeId = v;
  }

  public String getUri() {
    return this.uri;
  }

  public void setUri(String v) {
    this.uri = v;
  }

  public String getNotation() {
    return this.notation;
  }

  public void setNotation(String v) {
    this.notation = v;
  }

  public Integer getStatus() {
    return this.status;
  }

  public void setStatus(Integer v) {
    this.status = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
