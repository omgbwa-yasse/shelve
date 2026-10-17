package com.shelve.collaboration.entity;

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
@Table(name = "workplace_categories")
public class WorkplaceCategory {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, unique = true, length = 50)
  private String code;

  @Column(nullable = false, length = 191)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(length = 50)
  private String icon;

  @Column(length = 7)
  private String color;

  @Column(name = "is_active", nullable = false)
  private Boolean isActive = true;

  @Column(name = "display_order", nullable = false)
  private Integer displayOrder = 0;

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

  public Boolean getIsActive() {
    return this.isActive;
  }

  public void setIsActive(Boolean v) {
    this.isActive = v;
  }

  public Integer getDisplayOrder() {
    return this.displayOrder;
  }

  public void setDisplayOrder(Integer v) {
    this.displayOrder = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
