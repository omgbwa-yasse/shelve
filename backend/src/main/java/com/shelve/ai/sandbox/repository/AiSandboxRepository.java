package com.shelve.ai.sandbox.repository;

import com.shelve.ai.sandbox.entity.AiSandbox;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;

public interface AiSandboxRepository
    extends JpaRepository<AiSandbox, Long>, JpaSpecificationExecutor<AiSandbox> {
  Optional<AiSandbox> findByIdAndUserId(Long id, Long userId);

  List<AiSandbox> findByConversationIdAndUserId(Long conversationId, Long userId);

  boolean existsByFolder(String folder);

  List<AiSandbox> findByExpiresAtBefore(java.time.LocalDateTime now);
}
