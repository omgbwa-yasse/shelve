package com.shelve.retention.entity;

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
@Table(name = "declassement_comments")
public class DeclassementComment {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "declassement_list_id", nullable = false)
  private Long declassementListId;

  @Column(name = "user_id", nullable = false)
  private Long userId;

  @Column(nullable = false, columnDefinition = "text")
  private String content;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public Long getId() { return this.id; }
  public void setId(Long id) { this.id = id; }
  public Long getDeclassementListId() { return this.declassementListId; }
  public void setDeclassementListId(Long v) { this.declassementListId = v; }
  public Long getUserId() { return this.userId; }
  public void setUserId(Long v) { this.userId = v; }
  public String getContent() { return this.content; }
  public void setContent(String v) { this.content = v; }
  public Instant getCreatedAt() { return this.createdAt; }
  public Instant getUpdatedAt() { return this.updatedAt; }
}
