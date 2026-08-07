package com.shelve.slips.entity;

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
@Table(name = "slip_records")
public class SlipRecord {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "slip_id", nullable = false)
  private Long slipId;

  @Column(nullable = false, length = 10)
  private String code;

  @Column(nullable = false, columnDefinition = "text")
  private String name;

  @Column(name = "date_format", nullable = false, length = 1)
  private String dateFormat;

  @Column(name = "date_start", length = 10)
  private String dateStart;

  @Column(name = "date_end", length = 10)
  private String dateEnd;

  @Column(name = "date_exact")
  private LocalDate dateExact;

  @Column(columnDefinition = "text")
  private String content;

  @Column(name = "level_id", nullable = false)
  private Long levelId;

  @Column private Double width;

  @Column(name = "width_description", length = 100)
  private String widthDescription;

  @Column(name = "support_id", nullable = false)
  private Long supportId;

  @Column(name = "activity_id", nullable = false)
  private Long activityId;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

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

  public Long getSlipId() {
    return this.slipId;
  }

  public void setSlipId(Long v) {
    this.slipId = v;
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

  public String getDateFormat() {
    return this.dateFormat;
  }

  public void setDateFormat(String v) {
    this.dateFormat = v;
  }

  public String getDateStart() {
    return this.dateStart;
  }

  public void setDateStart(String v) {
    this.dateStart = v;
  }

  public String getDateEnd() {
    return this.dateEnd;
  }

  public void setDateEnd(String v) {
    this.dateEnd = v;
  }

  public LocalDate getDateExact() {
    return this.dateExact;
  }

  public void setDateExact(LocalDate v) {
    this.dateExact = v;
  }

  public String getContent() {
    return this.content;
  }

  public void setContent(String v) {
    this.content = v;
  }

  public Long getLevelId() {
    return this.levelId;
  }

  public void setLevelId(Long v) {
    this.levelId = v;
  }

  public Double getWidth() {
    return this.width;
  }

  public void setWidth(Double v) {
    this.width = v;
  }

  public String getWidthDescription() {
    return this.widthDescription;
  }

  public void setWidthDescription(String v) {
    this.widthDescription = v;
  }

  public Long getSupportId() {
    return this.supportId;
  }

  public void setSupportId(Long v) {
    this.supportId = v;
  }

  public Long getActivityId() {
    return this.activityId;
  }

  public void setActivityId(Long v) {
    this.activityId = v;
  }

  public Long getCreatorId() {
    return this.creatorId;
  }

  public void setCreatorId(Long v) {
    this.creatorId = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
