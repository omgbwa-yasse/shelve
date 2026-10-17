package com.shelve.ai.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;

@Entity
@Table(name = "ai_messages")
public class AiMessage {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(name = "conversation_id", nullable = false)
  private Long conversationId;

  @Column(nullable = false, length = 20)
  private String role;

  @Column(nullable = false, columnDefinition = "longtext")
  private String content;

  @Column(name = "context", columnDefinition = "json")
  private String context;

  @Column(name = "tokens_used")
  private Integer tokensUsed;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

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

  public String getRole() {
    return this.role;
  }

  public void setRole(String v) {
    this.role = v;
  }

  public String getContent() {
    return this.content;
  }

  public void setContent(String v) {
    this.content = v;
  }

  public String getContext() {
    return this.context;
  }

  public void setContext(String v) {
    this.context = v;
  }

  public Integer getTokensUsed() {
    return this.tokensUsed;
  }

  public void setTokensUsed(Integer v) {
    this.tokensUsed = v;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }
}
