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
@Table(name = "workplace_messages")
public class WorkplaceMessage {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "conversation_id", nullable = false)
  private Long conversationId;

  @Column(name = "user_id", nullable = false)
  private Long userId;

  @Column(nullable = false, columnDefinition = "text")
  private String content;

  @Column(name = "attachment_name", length = 255)
  private String attachmentName;

  @Column(name = "attachment_path", length = 500)
  private String attachmentPath;

  @Column(name = "attachment_mime", length = 120)
  private String attachmentMime;

  @Column(name = "attachment_size")
  private Long attachmentSize;

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

  public Long getConversationId() {
    return this.conversationId;
  }

  public void setConversationId(Long v) {
    this.conversationId = v;
  }

  public Long getUserId() {
    return this.userId;
  }

  public void setUserId(Long v) {
    this.userId = v;
  }

  public String getContent() {
    return this.content;
  }

  public void setContent(String v) {
    this.content = v;
  }

  public String getAttachmentName() {
    return this.attachmentName;
  }

  public void setAttachmentName(String v) {
    this.attachmentName = v;
  }

  public String getAttachmentPath() {
    return this.attachmentPath;
  }

  public void setAttachmentPath(String v) {
    this.attachmentPath = v;
  }

  public String getAttachmentMime() {
    return this.attachmentMime;
  }

  public void setAttachmentMime(String v) {
    this.attachmentMime = v;
  }

  public Long getAttachmentSize() {
    return this.attachmentSize;
  }

  public void setAttachmentSize(Long v) {
    this.attachmentSize = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
