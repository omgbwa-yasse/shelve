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
@Table(name = "thesaurus_schemes")
public class ThesaurusScheme {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "uri")
  private String uri;

  @Column(name = "identifier")
  private String identifier;

  @Column(name = "title")
  private String title;

  @Column(name = "description")
  private String description;

  @Column(name = "language")
  private String language = "fr-fr";

  @Column(name = "namespace_id")
  private Long namespaceId;

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

  public String getUri() {
    return this.uri;
  }

  public void setUri(String v) {
    this.uri = v;
  }

  public String getIdentifier() {
    return this.identifier;
  }

  public void setIdentifier(String v) {
    this.identifier = v;
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

  public String getLanguage() {
    return this.language;
  }

  public void setLanguage(String v) {
    this.language = v;
  }

  public Long getNamespaceId() {
    return this.namespaceId;
  }

  public void setNamespaceId(Long v) {
    this.namespaceId = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
