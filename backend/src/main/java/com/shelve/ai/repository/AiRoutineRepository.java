package com.shelve.ai.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.ai.entity.AiRoutine;

public interface AiRoutineRepository
    extends JpaRepository<AiRoutine, Long>, JpaSpecificationExecutor<AiRoutine> {}
