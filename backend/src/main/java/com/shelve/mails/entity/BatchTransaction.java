package com.shelve.mails.entity;

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
@Table(name = "batch_transactions")
public class BatchTransaction {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "batch_id")
  private Integer batchId;

  @Column(name = "organisation_send_id")
  private Long organisationSendId;

  @Column(name = "organisation_received_id")
  private Long organisationReceivedId;

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

  public Integer getBatchId() {
    return this.batchId;
  }

  public void setBatchId(Integer v) {
    this.batchId = v;
  }

  public Long getOrganisationSendId() {
    return this.organisationSendId;
  }

  public void setOrganisationSendId(Long v) {
    this.organisationSendId = v;
  }

  public Long getOrganisationReceivedId() {
    return this.organisationReceivedId;
  }

  public void setOrganisationReceivedId(Long v) {
    this.organisationReceivedId = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
