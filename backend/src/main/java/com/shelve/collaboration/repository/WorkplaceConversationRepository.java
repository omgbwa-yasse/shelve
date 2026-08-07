package com.shelve.collaboration.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.collaboration.entity.WorkplaceConversation;

public interface WorkplaceConversationRepository
    extends JpaRepository<WorkplaceConversation, Long>,
        JpaSpecificationExecutor<WorkplaceConversation> {}
