package com.shelve.collaboration.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.collaboration.entity.WorkplaceConversationParticipant;

public interface WorkplaceConversationParticipantRepository
    extends JpaRepository<WorkplaceConversationParticipant, Long>,
        JpaSpecificationExecutor<WorkplaceConversationParticipant> {
  List<WorkplaceConversationParticipant> findByConversationId(Long conversationId);

  List<WorkplaceConversationParticipant> findByConversationIdIn(List<Long> conversationIds);

  List<WorkplaceConversationParticipant> findByUserId(Long userId);
}
