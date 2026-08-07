package com.shelve.communications.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.communications.entity.Communication;

public interface CommunicationRepository
    extends JpaRepository<Communication, Long>, JpaSpecificationExecutor<Communication> {}
