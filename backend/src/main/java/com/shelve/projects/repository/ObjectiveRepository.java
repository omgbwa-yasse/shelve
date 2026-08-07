package com.shelve.projects.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.projects.entity.Objective;

public interface ObjectiveRepository
    extends JpaRepository<Objective, Long>, JpaSpecificationExecutor<Objective> {}
