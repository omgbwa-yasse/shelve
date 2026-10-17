package com.shelve.collaboration.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.collaboration.entity.WorkplaceMessage;

public interface WorkplaceMessageRepository
    extends JpaRepository<WorkplaceMessage, Long>, JpaSpecificationExecutor<WorkplaceMessage> {
  List<WorkplaceMessage> findByConversationIdOrderByCreatedAtAsc(Long conversationId);

  List<WorkplaceMessage> findByConversationIdInOrderByCreatedAtAsc(List<Long> conversationIds);
}
