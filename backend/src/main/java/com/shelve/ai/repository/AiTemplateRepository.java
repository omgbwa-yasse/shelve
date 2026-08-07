package com.shelve.ai.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.ai.entity.AiTemplate;

public interface AiTemplateRepository
    extends JpaRepository<AiTemplate, Long>, JpaSpecificationExecutor<AiTemplate> {}
