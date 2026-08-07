package com.shelve.ai.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.ai.entity.Prompt;

public interface PromptRepository
    extends JpaRepository<Prompt, Long>, JpaSpecificationExecutor<Prompt> {}
