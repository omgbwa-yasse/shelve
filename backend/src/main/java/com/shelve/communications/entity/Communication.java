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
@Table(name = "communications")
public class Communication {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 10)
  private String code;

  @Column(nullable = false, length = 200)
  private String name;

  @Column(columnDefinition = "text")
  private String content;

  @Column(name = "operator_id", nullable = false)
  private Long operatorId;

  @Column(name = "operator_organisation_id", nullable = false)
  private Long operatorOrganisationId;

  @Column(name = "user_id", nullable = false)
  private Long userId;

  @Column(name = "user_organisation_id", nullable = false)
  private Long userOrganisationId;

  @Column(name = "return_date", nullable = false)
  private LocalDate returnDate;

  @Column(name = "return_effective")
  private LocalDate returnEffective;

  @Column(nullable = false, length = 20)
  private String status = "pending";

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

  public String getContent() {
    return this.content;
  }

  public void setContent(String v) {
    this.content = v;
  }

  public Long getOperatorId() {
    return this.operatorId;
  }

  public void setOperatorId(Long v) {
    this.operatorId = v;
  }

  public Long getOperatorOrganisationId() {
    return this.operatorOrganisationId;
  }

  public void setOperatorOrganisationId(Long v) {
    this.operatorOrganisationId = v;
  }

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long v) {
    this.userId = v;
  }

  public Long getUserOrganisationId() {
    return this.userOrganisationId;
  }

  public void setUserOrganisationId(Long v) {
    this.userOrganisationId = v;
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

  public String getStatus() {
    return this.status;
  }

  public void setStatus(String v) {
    this.status = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
