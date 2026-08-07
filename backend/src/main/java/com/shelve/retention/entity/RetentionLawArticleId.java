package com.shelve.retention.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Embeddable;
import java.io.Serializable;
import java.util.Objects;

@Embeddable
public class RetentionLawArticleId implements Serializable {
  @Column(name = "retention_id", nullable = false)
  private Long retentionId;

  @Column(name = "law_article_id", nullable = false)
  private Long lawArticleId;

  public RetentionLawArticleId() {}

  public RetentionLawArticleId(Long retentionId, Long lawArticleId) {
    this.retentionId = retentionId;
    this.lawArticleId = lawArticleId;
  }

  public Long getRetentionId() {
    return this.retentionId;
  }

  public void setRetentionId(Long v) {
    this.retentionId = v;
  }

  public Long getLawArticleId() {
    return this.lawArticleId;
  }

  public void setLawArticleId(Long v) {
    this.lawArticleId = v;
  }

  public boolean equals(Object o) {
    if (this == o) {
      return true;
    }
    if (!(o instanceof RetentionLawArticleId)) {
      return false;
    }
    RetentionLawArticleId that = (RetentionLawArticleId) o;
    return Objects.equals(this.retentionId, that.retentionId)
        && Objects.equals(this.lawArticleId, that.lawArticleId);
  }

  public int hashCode() {
    return Objects.hash(this.retentionId, this.lawArticleId);
  }
}
