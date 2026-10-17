package com.shelve.referentials.entity;

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
@Table(name = "law_articles")
public class LawArticle {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, length = 10)
  private String code;

  @Column(nullable = false, length = 200)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(name = "law_id", nullable = false)
  private Long lawId;

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
  public Long getLawId() { return this.lawId; }
  public void setLawId(Long lawId) { this.lawId = lawId; }
  public Instant getCreatedAt() { return this.createdAt; }
  public Instant getUpdatedAt() { return this.updatedAt; }
}
