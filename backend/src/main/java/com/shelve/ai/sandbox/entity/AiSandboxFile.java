package com.shelve.ai.sandbox.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;

/** Métadonnée d'un fichier du workspace d'un sandbox (table `ai_sandbox_files`). */
@Entity
@Table(name = "ai_sandbox_files")
public class AiSandboxFile {
  public static final String SECTION_INPUT = "input";
  public static final String SECTION_CORE = "core";
  public static final String SECTION_REFERENCE = "reference";
  public static final String SECTION_OUTPUT = "output";
  public static final String SECTION_LOGS = "logs";

  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "sandbox_id")
  private Long sandboxId;

  @Column(name = "section")
  private String section;

  @Column(name = "path")
  private String path;

  @Column(name = "name")
  private String name;

  @Column(name = "size")
  private Long size = 0L;

  @Column(name = "mime")
  private String mime;

  @Column(name = "hash")
  private String hash;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  public Long getId() {
    return this.id;
  }

  public void setId(Long id) {
    this.id = id;
  }

  public Long getSandboxId() {
    return this.sandboxId;
  }

  public void setSandboxId(Long v) {
    this.sandboxId = v;
  }

  public String getSection() {
    return this.section;
  }

  public void setSection(String v) {
    this.section = v;
  }

  public String getPath() {
    return this.path;
  }

  public void setPath(String v) {
    this.path = v;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String v) {
    this.name = v;
  }

  public Long getSize() {
    return this.size;
  }

  public void setSize(Long v) {
    this.size = v;
  }

  public String getMime() {
    return this.mime;
  }

  public void setMime(String v) {
    this.mime = v;
  }

  public String getHash() {
    return this.hash;
  }

  public void setHash(String v) {
    this.hash = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }
}
