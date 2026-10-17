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
@Table(name = "declassement_lists")
public class DeclassementList {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 20)
  private String code;

  @Column(nullable = false, length = 200)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(name = "organisation_id", nullable = false)
  private Long organisationId;

  @Column(name = "declassement_status_id", nullable = false)
  private Long declassementStatusId;

  @Column(name = "query_criteria", columnDefinition = "json")
  private String queryCriteria;

  @Column(name = "digital_support", nullable = false)
  private Boolean digitalSupport = false;

  @Column(name = "analog_support", nullable = false)
  private Boolean analogSupport = false;

  @Column(name = "include_subrecords", nullable = false)
  private Boolean includeSubrecords = false;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @Column(name = "is_approval_requested")
  private Boolean isApprovalRequested = false;

  @Column(name = "approval_requested_date")
  private Instant approvalRequestedDate;

  @Column(name = "approval_requested_by")
  private Long approvalRequestedBy;

  @Column(name = "is_approved")
  private Boolean isApproved = false;

  @Column(name = "approved_date")
  private Instant approvedDate;

  @Column(name = "approved_by")
  private Long approvedBy;

  @Column(name = "is_validated")
  private Boolean isValidated = false;

  @Column(name = "validated_date")
  private Instant validatedDate;

  @Column(name = "validated_by")
  private Long validatedBy;

  @Column(name = "is_treated")
  private Boolean isTreated = false;

  @Column(name = "treated_date")
  private Instant treatedDate;

  @Column(name = "treated_by")
  private Long treatedBy;

  @Column(name = "rejection_reason", columnDefinition = "text")
  private String rejectionReason;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  public Long getId() { return this.id; }
  public void setId(Long id) { this.id = id; }
  public String getCode() { return this.code; }
  public void setCode(String code) { this.code = code; }
  public String getName() { return this.name; }
  public void setName(String name) { this.name = name; }
  public String getDescription() { return this.description; }
  public void setDescription(String description) { this.description = description; }
  public Long getOrganisationId() { return this.organisationId; }
  public void setOrganisationId(Long organisationId) { this.organisationId = organisationId; }
  public Long getDeclassementStatusId() { return this.declassementStatusId; }
  public void setDeclassementStatusId(Long id) { this.declassementStatusId = id; }
  public String getQueryCriteria() { return this.queryCriteria; }
  public void setQueryCriteria(String queryCriteria) { this.queryCriteria = queryCriteria; }
  public Boolean getDigitalSupport() { return this.digitalSupport; }
  public void setDigitalSupport(Boolean v) { this.digitalSupport = v; }
  public Boolean getAnalogSupport() { return this.analogSupport; }
  public void setAnalogSupport(Boolean v) { this.analogSupport = v; }
  public Boolean getIncludeSubrecords() { return this.includeSubrecords; }
  public void setIncludeSubrecords(Boolean v) { this.includeSubrecords = v; }
  public Long getCreatorId() { return this.creatorId; }
  public void setCreatorId(Long creatorId) { this.creatorId = creatorId; }
  public Boolean getIsApprovalRequested() { return this.isApprovalRequested; }
  public void setIsApprovalRequested(Boolean v) { this.isApprovalRequested = v; }
  public Instant getApprovalRequestedDate() { return this.approvalRequestedDate; }
  public void setApprovalRequestedDate(Instant v) { this.approvalRequestedDate = v; }
  public Long getApprovalRequestedBy() { return this.approvalRequestedBy; }
  public void setApprovalRequestedBy(Long v) { this.approvalRequestedBy = v; }
  public Boolean getIsApproved() { return this.isApproved; }
  public void setIsApproved(Boolean v) { this.isApproved = v; }
  public Instant getApprovedDate() { return this.approvedDate; }
  public void setApprovedDate(Instant v) { this.approvedDate = v; }
  public Long getApprovedBy() { return this.approvedBy; }
  public void setApprovedBy(Long v) { this.approvedBy = v; }
  public Boolean getIsValidated() { return this.isValidated; }
  public void setIsValidated(Boolean v) { this.isValidated = v; }
  public Instant getValidatedDate() { return this.validatedDate; }
  public void setValidatedDate(Instant v) { this.validatedDate = v; }
  public Long getValidatedBy() { return this.validatedBy; }
  public void setValidatedBy(Long v) { this.validatedBy = v; }
  public Boolean getIsTreated() { return this.isTreated; }
  public void setIsTreated(Boolean v) { this.isTreated = v; }
  public Instant getTreatedDate() { return this.treatedDate; }
  public void setTreatedDate(Instant v) { this.treatedDate = v; }
  public Long getTreatedBy() { return this.treatedBy; }
  public void setTreatedBy(Long v) { this.treatedBy = v; }
  public String getRejectionReason() { return this.rejectionReason; }
  public void setRejectionReason(String v) { this.rejectionReason = v; }
  public Instant getCreatedAt() { return this.createdAt; }
  public Instant getUpdatedAt() { return this.updatedAt; }
}
