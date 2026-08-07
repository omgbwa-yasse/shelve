package com.shelve.referentials.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.Communicability;

public interface CommunicabilityRepository
    extends JpaRepository<Communicability, Long>, JpaSpecificationExecutor<Communicability> {}
