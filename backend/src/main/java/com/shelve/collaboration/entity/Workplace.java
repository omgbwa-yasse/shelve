package com.shelve.collaboration.entity;

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
@Table(name = "workplaces")
public class Workplace {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "code")
  private String code;

  @Column(name = "name")
  private String name;

  @Column(name = "description")
  private String description;

  @Column(name = "category_id")
  private Long categoryId;

  @Column(name = "icon")
  private String icon;

  @Column(name = "color")
  private String color = "#3498db";

  @Column(name = "settings")
  private String settings;

  @Column(name = "is_public")
  private Boolean isPublic = false;

  @Column(name = "allow_external_sharing")
  private Boolean allowExternalSharing = false;

  @Column(name = "max_members")
  private Integer maxMembers = 50;

  @Column(name = "max_storage_mb")
  private Integer maxStorageMb = 5120;

  @Column(name = "members_count")
  private Integer membersCount = 0;

  @Column(name = "folders_count")
  private Integer foldersCount = 0;

  @Column(name = "documents_count")
  private Integer documentsCount = 0;

  @Column(name = "storage_used_bytes")
  private Long storageUsedBytes = 0L;

  @Column(name = "status")
  private String status = "active";

  @Column(name = "start_date")
  private LocalDate startDate;

  @Column(name = "end_date")
  private LocalDate endDate;

  @Column(name = "archived_at")
  private Instant archivedAt;

  @Column(name = "organisation_id")
  private Long organisationId;

  @Column(name = "owner_id")
  private Long ownerId;

  @Column(name = "created_by")
  private Long createdBy;

  @Column(name = "updated_by")
  private Long updatedBy;

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

  public Long getCategoryId() {
    return this.categoryId;
  }

  public void setCategoryId(Long v) {
    this.categoryId = v;
  }

  public String getIcon() {
    return this.icon;
  }

  public void setIcon(String v) {
    this.icon = v;
  }

  public String getColor() {
    return this.color;
  }

  public void setColor(String v) {
    this.color = v;
  }

  public String getSettings() {
    return this.settings;
  }

  public void setSettings(String v) {
    this.settings = v;
  }

  public Boolean getIsPublic() {
    return this.isPublic;
  }

  public void setIsPublic(Boolean v) {
    this.isPublic = v;
  }

  public Boolean getAllowExternalSharing() {
    return this.allowExternalSharing;
  }

  public void setAllowExternalSharing(Boolean v) {
    this.allowExternalSharing = v;
  }

  public Integer getMaxMembers() {
    return this.maxMembers;
  }

  public void setMaxMembers(Integer v) {
    this.maxMembers = v;
  }

  public Integer getMaxStorageMb() {
    return this.maxStorageMb;
  }

  public void setMaxStorageMb(Integer v) {
    this.maxStorageMb = v;
  }

  public Integer getMembersCount() {
    return this.membersCount;
  }

  public void setMembersCount(Integer v) {
    this.membersCount = v;
  }

  public Integer getFoldersCount() {
    return this.foldersCount;
  }

  public void setFoldersCount(Integer v) {
    this.foldersCount = v;
  }

  public Integer getDocumentsCount() {
    return this.documentsCount;
  }

  public void setDocumentsCount(Integer v) {
    this.documentsCount = v;
  }

  public Long getStorageUsedBytes() {
    return this.storageUsedBytes;
  }

  public void setStorageUsedBytes(Long v) {
    this.storageUsedBytes = v;
  }

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
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

  public Instant getArchivedAt() {
    return this.archivedAt;
  }

  public void setArchivedAt(Instant v) {
    this.archivedAt = v;
  }

  public Long getOrganisationId() {
    return this.organisationId;
  }

  public void setOrganisationId(Long v) {
    this.organisationId = v;
  }

  public Long getOwnerId() {
    return this.ownerId;
  }

  public void setOwnerId(Long v) {
    this.ownerId = v;
  }

  public Long getCreatedBy() {
    return this.createdBy;
  }

  public void setCreatedBy(Long v) {
    this.createdBy = v;
  }

  public Long getUpdatedBy() {
    return this.updatedBy;
  }

  public void setUpdatedBy(Long v) {
    this.updatedBy = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
