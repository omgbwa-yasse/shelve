package com.shelve.retention.entity;

import jakarta.persistence.Column;
import jakarta.persistence.EmbeddedId;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "retention_law_articles")
public class RetentionLawArticle {
  @EmbeddedId private RetentionLawArticleId id;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public RetentionLawArticleId getId() {
    return this.id;
  }

  public void setId(RetentionLawArticleId id) {
    this.id = id;
  }

  public Long getRetentionId() {
    return this.id != null ? this.id.getRetentionId() : null;
  }

  public Long getLawArticleId() {
    return this.id != null ? this.id.getLawArticleId() : null;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
