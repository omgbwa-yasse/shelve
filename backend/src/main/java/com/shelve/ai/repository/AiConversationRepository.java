package com.shelve.ai.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.ai.entity.AiConversation;

public interface AiConversationRepository
    extends JpaRepository<AiConversation, Long>, JpaSpecificationExecutor<AiConversation> {}
