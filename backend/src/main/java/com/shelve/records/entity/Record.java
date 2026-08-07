package com.shelve.records.entity;
import com.shelve.common.Json;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import java.math.BigDecimal;
import java.time.Instant;
import java.time.LocalDate;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.SQLDelete;
import org.hibernate.annotations.SQLRestriction;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "records")
@SQLRestriction(value = "deleted_at is null")
@SQLDelete(sql = "update records set deleted_at = now(), updated_at = now() where id = ?")
public class Record {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 30)
  private String code;

  @Column(nullable = false, length = 191)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(name = "opening_date")
  private LocalDate openingDate;

  @Column(name = "closing_date")
  private LocalDate closingDate;

  @Column(name = "old_record_number", length = 191)
  private String oldRecordNumber;

  @Column(nullable = false)
  private Boolean unavailable = false;

  @Column(name = "annual_opening", nullable = false)
  private Boolean annualOpening = false;

  @Column(name = "is_essential", nullable = false)
  private Boolean isEssential = false;

  @Column(name = "loaned_to")
  private Long loanedTo;

  @Column(name = "loaned_at")
  private Instant loanedAt;

  @Column(name = "loan_planned_return_at")
  private Instant loanPlannedReturnAt;

  @Column(name = "loan_actual_return_at")
  private Instant loanActualReturnAt;

  @Column(name = "modified_after_loan", nullable = false)
  private Boolean modifiedAfterLoan = false;

  @Column(name = "confidentiality_id")
  private Long confidentialityId;

  @Column(name = "access_limit_id")
  private Long accessLimitId;

  @Column(name = "publication_date")
  private LocalDate publicationDate;

  @Column(name = "location_before_add", length = 191)
  private String locationBeforeAdd;

  @Column(name = "type_id")
  private Long typeId;

  @Column(name = "level_id", nullable = false)
  private Long levelId;

  @Column(name = "status_id", nullable = false)
  private Long statusId;

  @Column(name = "activity_id")
  private Long activityId;

  @Column(name = "parent_id")
  private Long parentId;

  @Column(name = "organisation_id", nullable = false)
  private Long organisationId;

  @Column(name = "workplace_id")
  private Long workplaceId;

  @Column(name = "is_workplace_folder", nullable = false)
  private Boolean isWorkplaceFolder = false;

  @Column(name = "is_workplace_shared", nullable = false)
  private Boolean isWorkplaceShared = false;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @Column(name = "assigned_to")
  private Long assignedTo;

  @Column(name = "access_level", nullable = false, length = 20)
  private String accessLevel = "internal";

  @Column(name = "requires_approval", nullable = false)
  private Boolean requiresApproval = false;

  @Column(name = "approved_by")
  private Long approvedBy;

  @Column(name = "approved_at")
  private Instant approvedAt;

  @Column(columnDefinition = "json")
  private String metadata;

  @Column(name = "linear_measure_cm", precision = 10, scale = 2)
  private BigDecimal linearMeasureCm;

  @Column(name = "start_date")
  private LocalDate startDate;

  @Column(name = "end_date")
  private LocalDate endDate;

  @Column(name = "date_exact")
  private LocalDate dateExact;

  @Column(name = "date_format", length = 1)
  private String dateFormat;

  @Column(name = "version_number", nullable = false)
  private Integer versionNumber = 1;

  @Column(name = "is_current_version", nullable = false)
  private Boolean isCurrentVersion = true;

  @Column(name = "legacy_source", length = 20)
  private String legacySource;

  @Column(name = "legacy_id")
  private Long legacyId;

  @ManyToOne(fetch = FetchType.LAZY)
  @JoinColumn(name = "type_id", insertable = false, updatable = false)
  private RecordType type;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  @Column(name = "deleted_at", insertable = false, updatable = false)
  private Instant deletedAt;

  public boolean isRoot() {
    return this.parentId == null;
  }

  public Long getId() {
    return this.id;
  }

  public void setId(Long id) {
    this.id = id;
  }

  public String getCode() {
    return this.code;
  }

  public void setCode(String v) {
    this.code = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String v) {
    this.description = v;
  }

  public LocalDate getOpeningDate() {
    return this.openingDate;
  }

  public void setOpeningDate(LocalDate v) {
    this.openingDate = v;
  }

  public LocalDate getClosingDate() {
    return this.closingDate;
  }

  public void setClosingDate(LocalDate v) {
    this.closingDate = v;
  }

  public String getOldRecordNumber() {
    return this.oldRecordNumber;
  }

  public void setOldRecordNumber(String v) {
    this.oldRecordNumber = v;
  }

  public Boolean getUnavailable() {
    return this.unavailable;
  }

  public void setUnavailable(Boolean v) {
    this.unavailable = v;
  }

  public Boolean getAnnualOpening() {
    return this.annualOpening;
  }

  public void setAnnualOpening(Boolean v) {
    this.annualOpening = v;
  }

  public Boolean getIsEssential() {
    return this.isEssential;
  }

  public void setIsEssential(Boolean v) {
    this.isEssential = v;
  }

  public Long getLoanedTo() {
    return this.loanedTo;
  }

  public void setLoanedTo(Long v) {
    this.loanedTo = v;
  }

  public Instant getLoanedAt() {
    return this.loanedAt;
  }

  public void setLoanedAt(Instant v) {
    this.loanedAt = v;
  }

  public Instant getLoanPlannedReturnAt() {
    return this.loanPlannedReturnAt;
  }

  public void setLoanPlannedReturnAt(Instant v) {
    this.loanPlannedReturnAt = v;
  }

  public Instant getLoanActualReturnAt() {
    return this.loanActualReturnAt;
  }

  public void setLoanActualReturnAt(Instant v) {
    this.loanActualReturnAt = v;
  }

  public Boolean getModifiedAfterLoan() {
    return this.modifiedAfterLoan;
  }

  public void setModifiedAfterLoan(Boolean v) {
    this.modifiedAfterLoan = v;
  }

  public Long getConfidentialityId() {
    return this.confidentialityId;
  }

  public void setConfidentialityId(Long v) {
    this.confidentialityId = v;
  }

  public Long getAccessLimitId() {
    return this.accessLimitId;
  }

  public void setAccessLimitId(Long v) {
    this.accessLimitId = v;
  }

  public LocalDate getPublicationDate() {
    return this.publicationDate;
  }

  public void setPublicationDate(LocalDate v) {
    this.publicationDate = v;
  }

  public String getLocationBeforeAdd() {
    return this.locationBeforeAdd;
  }

  public void setLocationBeforeAdd(String v) {
    this.locationBeforeAdd = v;
  }

  public Long getTypeId() {
    return this.typeId;
  }

  public void setTypeId(Long v) {
    this.typeId = v;
  }

  public Long getLevelId() {
    return this.levelId;
  }

  public void setLevelId(Long v) {
    this.levelId = v;
  }

  public Long getStatusId() {
    return this.statusId;
  }

  public void setStatusId(Long v) {
    this.statusId = v;
  }

  public Long getActivityId() {
    return this.activityId;
  }

  public void setActivityId(Long v) {
    this.activityId = v;
  }

  public Long getParentId() {
    return this.parentId;
  }

  public void setParentId(Long v) {
    this.parentId = v;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long v) {
    this.organisationId = v;
  }

  public Long getWorkplaceId() {
    return this.workplaceId;
  }

  public void setWorkplaceId(Long v) {
    this.workplaceId = v;
  }

  public Boolean getIsWorkplaceFolder() {
    return this.isWorkplaceFolder;
  }

  public void setIsWorkplaceFolder(Boolean v) {
    this.isWorkplaceFolder = v;
  }

  public Boolean getIsWorkplaceShared() {
    return this.isWorkplaceShared;
  }

  public void setIsWorkplaceShared(Boolean v) {
    this.isWorkplaceShared = v;
  }

  public Long getCreatorId() {
    return this.creatorId;
  }

  public void setCreatorId(Long v) {
    this.creatorId = v;
  }

  public Long getAssignedTo() {
    return this.assignedTo;
  }

  public void setAssignedTo(Long v) {
    this.assignedTo = v;
  }

  public String getAccessLevel() {
    return this.accessLevel;
  }

  public void setAccessLevel(String v) {
    this.accessLevel = v;
  }

  public Boolean getRequiresApproval() {
    return this.requiresApproval;
  }

  public void setRequiresApproval(Boolean v) {
    this.requiresApproval = v;
  }

  public Long getApprovedBy() {
    return this.approvedBy;
  }

  public void setApprovedBy(Long v) {
    this.approvedBy = v;
  }

  public Instant getApprovedAt() {
    return this.approvedAt;
  }

  public void setApprovedAt(Instant v) {
    this.approvedAt = v;
  }

  public String getMetadata() {
    return this.metadata;
  }

  public void setMetadata(String v) {
    this.metadata = v;
  }

  public BigDecimal getLinearMeasureCm() {
    return this.linearMeasureCm;
  }

  public void setLinearMeasureCm(BigDecimal v) {
    this.linearMeasureCm = v;
  }

  public LocalDate getStartDate() {
    return this.startDate;
  }

  public void setStartDate(LocalDate v) {
    this.startDate = v;
  }

  public LocalDate getEndDate() {
    return this.endDate;
  }

  public void setEndDate(LocalDate v) {
    this.endDate = v;
  }

  public LocalDate getDateExact() {
    return this.dateExact;
  }

  public void setDateExact(LocalDate v) {
    this.dateExact = v;
  }

  public String getDateFormat() {
    return this.dateFormat;
  }

  public void setDateFormat(String v) {
    this.dateFormat = v;
  }

  public Integer getVersionNumber() {
    return this.versionNumber;
  }

  public void setVersionNumber(Integer v) {
    this.versionNumber = v;
  }

  public Boolean getIsCurrentVersion() {
    return this.isCurrentVersion;
  }

  public void setIsCurrentVersion(Boolean v) {
    this.isCurrentVersion = v;
  }

  public String getLegacySource() {
    return this.legacySource;
  }

  public void setLegacySource(String v) {
    this.legacySource = v;
  }

  public Long getLegacyId() {
    return this.legacyId;
  }

  public void setLegacyId(Long v) {
    this.legacyId = v;
  }

  public RecordType getType() {
    return this.type;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }

  public Instant getDeletedAt() {
    return this.deletedAt;
  }
}
