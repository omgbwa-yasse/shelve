package com.shelve.collaboration.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.collaboration.entity.WorkplaceTemplate;

public interface WorkplaceTemplateRepository
    extends JpaRepository<WorkplaceTemplate, Long>, JpaSpecificationExecutor<WorkplaceTemplate> {}
