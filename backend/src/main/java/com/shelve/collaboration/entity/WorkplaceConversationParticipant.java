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
@Table(name = "workplace_conversation_participants")
public class WorkplaceConversationParticipant {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "conversation_id", nullable = false)
  private Long conversationId;

  @Column(name = "user_id", nullable = false)
  private Long userId;

  @Column(nullable = false, length = 20)
  private String role = "member";

  @Column(name = "last_read_at")
  private Instant lastReadAt;

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

  public String getRole() {
    return this.role;
  }

  public void setRole(String v) {
    this.role = v;
  }

  public Instant getLastReadAt() {
    return this.lastReadAt;
  }

  public void setLastReadAt(Instant v) {
    this.lastReadAt = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
