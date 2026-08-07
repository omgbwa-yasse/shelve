package com.shelve.collaboration.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.collaboration.entity.Workplace;

public interface WorkplaceRepository
    extends JpaRepository<Workplace, Long>, JpaSpecificationExecutor<Workplace> {}
