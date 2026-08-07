package com.shelve.localisation.entity;

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
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "containers")
public class Container {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 20)
  private String code;

  @Column(name = "shelve_id", nullable = false)
  private Long shelveId;

  @Column(name = "status_id", nullable = false)
  private Long statusId;

  @Column(name = "property_id", nullable = false)
  private Long propertyId;

  @Column(name = "capacity_cm", precision = 10, scale = 2)
  private BigDecimal capacityCm;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @Column(name = "creator_organisation_id", nullable = false)
  private Long creatorOrganisationId;

  @Column(name = "is_archived", nullable = false)
  private Boolean isArchived = false;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  @ManyToOne(fetch = FetchType.LAZY)
  @JoinColumn(name = "shelve_id", insertable = false, updatable = false)
  private Shelf shelf;

  public Shelf getShelf() {
    return this.shelf;
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

  public void setCode(String code) {
    this.code = code;
  }

  public Long getShelveId() {
    return this.shelveId;
  }

  public void setShelveId(Long shelveId) {
    this.shelveId = shelveId;
  }

  public Long getStatusId() {
    return this.statusId;
  }

  public void setStatusId(Long statusId) {
    this.statusId = statusId;
  }

  public Long getPropertyId() {
    return this.propertyId;
  }

  public void setPropertyId(Long propertyId) {
    this.propertyId = propertyId;
  }

  public BigDecimal getCapacityCm() {
    return this.capacityCm;
  }

  public void setCapacityCm(BigDecimal capacityCm) {
    this.capacityCm = capacityCm;
  }

  public Long getCreatorId() {
    return this.creatorId;
  }

  public void setCreatorId(Long creatorId) {
    this.creatorId = creatorId;
  }

  public Long getCreatorOrganisationId() {
    return this.creatorOrganisationId;
  }

  public void setCreatorOrganisationId(Long creatorOrganisationId) {
    this.creatorOrganisationId = creatorOrganisationId;
  }

  public Boolean getIsArchived() {
    return this.isArchived;
  }

  public void setIsArchived(Boolean isArchived) {
    this.isArchived = isArchived;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
