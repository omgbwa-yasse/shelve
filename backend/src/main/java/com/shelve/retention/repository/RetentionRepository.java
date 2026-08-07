package com.shelve.retention.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.Retention;

public interface RetentionRepository
    extends JpaRepository<Retention, Long>, JpaSpecificationExecutor<Retention> {}
