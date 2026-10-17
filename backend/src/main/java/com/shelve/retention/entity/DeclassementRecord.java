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
@Table(name = "declassement_records")
public class DeclassementRecord {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "declassement_list_id", nullable = false)
  private Long declassementListId;

  @Column(name = "record_id", nullable = false)
  private Long recordId;

  @Column(name = "added_by", nullable = false)
  private Long addedBy;

  @Column(columnDefinition = "text")
  private String comment;

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
  public Long getRecordId() { return this.recordId; }
  public void setRecordId(Long v) { this.recordId = v; }
  public Long getAddedBy() { return this.addedBy; }
  public void setAddedBy(Long v) { this.addedBy = v; }
  public String getComment() { return this.comment; }
  public void setComment(String v) { this.comment = v; }
  public Instant getCreatedAt() { return this.createdAt; }
  public Instant getUpdatedAt() { return this.updatedAt; }
}
