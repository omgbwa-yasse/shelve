package com.shelve.communications.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import java.time.LocalDate;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "communication_record")
public class CommunicationRecord {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "communication_id", nullable = false)
  private Long communicationId;

  @Column(name = "record_id", nullable = false)
  private Long recordId;

  @Column(columnDefinition = "text")
  private String content;

  @Column(name = "is_original", nullable = false)
  private Boolean isOriginal = false;

  @Column(name = "return_date", nullable = false)
  private LocalDate returnDate;

  @Column(name = "return_effective")
  private LocalDate returnEffective;

  @Column(name = "operator_id", nullable = false)
  private Long operatorId;

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

  public Long getCommunicationId() {
    return this.communicationId;
  }

  public void setCommunicationId(Long v) {
    this.communicationId = v;
  }

  public Long getRecordId() {
    return this.recordId;
  }

  public void setRecordId(Long v) {
    this.recordId = v;
  }

  public String getContent() {
    return this.content;
  }

  public void setContent(String v) {
    this.content = v;
  }

  public Boolean getIsOriginal() {
    return this.isOriginal;
  }

  public void setIsOriginal(Boolean v) {
    this.isOriginal = v;
  }

  public LocalDate getReturnDate() {
    return this.returnDate;
  }

  public void setReturnDate(LocalDate v) {
    this.returnDate = v;
  }

  public LocalDate getReturnEffective() {
    return this.returnEffective;
  }

  public void setReturnEffective(LocalDate v) {
    this.returnEffective = v;
  }

  public Long getOperatorId() {
    return this.operatorId;
  }

  public void setOperatorId(Long v) {
    this.operatorId = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
