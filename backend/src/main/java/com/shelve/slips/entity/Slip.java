package com.shelve.slips.entity;

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
@Table(name = "slips")
public class Slip {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 20)
  private String code;

  @Column(nullable = false, length = 200)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(name = "officer_organisation_id", nullable = false)
  private Long officerOrganisationId;

  @Column(name = "officer_id", nullable = false)
  private Long officerId;

  @Column(name = "user_organisation_id", nullable = false)
  private Long userOrganisationId;

  @Column(name = "user_id")
  private Long userId;

  @Column(name = "slip_status_id", nullable = false)
  private Long slipStatusId;

  @Column(name = "is_received")
  private Boolean isReceived = false;

  @Column(name = "received_date")
  private Instant receivedDate;

  @Column(name = "received_by")
  private Long receivedBy;

  @Column(name = "is_approved")
  private Boolean isApproved = false;

  @Column(name = "approved_date")
  private Instant approvedDate;

  @Column(name = "approved_by")
  private Long approvedBy;

  @Column(name = "is_integrated")
  private Boolean isIntegrated = false;

  @Column(name = "integrated_date")
  private Instant integratedDate;

  @Column(name = "integrated_by")
  private Long integratedBy;

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

  public String getCode() {
    return this.code;
  }

  public void setCode(String code) {
    this.code = code;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String name) {
    this.name = name;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String description) {
    this.description = description;
  }

  public Long getOfficerOrganisationId() {
    return this.officerOrganisationId;
  }

  public void setOfficerOrganisationId(Long v) {
    this.officerOrganisationId = v;
  }

  public Long getOfficerId() {
    return this.officerId;
  }

  public void setOfficerId(Long v) {
    this.officerId = v;
  }

  public Long getUserOrganisationId() {
    return this.userOrganisationId;
  }

  public void setUserOrganisationId(Long v) {
    this.userOrganisationId = v;
  }

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long v) {
    this.userId = v;
  }

  public Long getSlipStatusId() {
    return this.slipStatusId;
  }

  public void setSlipStatusId(Long v) {
    this.slipStatusId = v;
  }

  public Boolean getIsReceived() {
    return this.isReceived;
  }

  public void setIsReceived(Boolean v) {
    this.isReceived = v;
  }

  public Instant getReceivedDate() {
    return this.receivedDate;
  }

  public void setReceivedDate(Instant v) {
    this.receivedDate = v;
  }

  public Long getReceivedBy() {
    return this.receivedBy;
  }

  public void setReceivedBy(Long v) {
    this.receivedBy = v;
  }

  public Boolean getIsApproved() {
    return this.isApproved;
  }

  public void setIsApproved(Boolean v) {
    this.isApproved = v;
  }

  public Instant getApprovedDate() {
    return this.approvedDate;
  }

  public void setApprovedDate(Instant v) {
    this.approvedDate = v;
  }

  public Long getApprovedBy() {
    return this.approvedBy;
  }

  public void setApprovedBy(Long v) {
    this.approvedBy = v;
  }

  public Boolean getIsIntegrated() {
    return this.isIntegrated;
  }

  public void setIsIntegrated(Boolean v) {
    this.isIntegrated = v;
  }

  public Instant getIntegratedDate() {
    return this.integratedDate;
  }

  public void setIntegratedDate(Instant v) {
    this.integratedDate = v;
  }

  public Long getIntegratedBy() {
    return this.integratedBy;
  }

  public void setIntegratedBy(Long v) {
    this.integratedBy = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
