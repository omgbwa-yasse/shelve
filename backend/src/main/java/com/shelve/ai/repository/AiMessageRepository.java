package com.shelve.ai.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.ai.entity.AiMessage;

public interface AiMessageRepository
    extends JpaRepository<AiMessage, Long>, JpaSpecificationExecutor<AiMessage> {
  List<AiMessage> findByConversationIdOrderByCreatedAtAsc(Long conversationId);
}
