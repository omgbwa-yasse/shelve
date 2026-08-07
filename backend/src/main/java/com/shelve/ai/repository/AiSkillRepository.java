package com.shelve.ai.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.ai.entity.AiSkill;

public interface AiSkillRepository
    extends JpaRepository<AiSkill, Long>, JpaSpecificationExecutor<AiSkill> {}
