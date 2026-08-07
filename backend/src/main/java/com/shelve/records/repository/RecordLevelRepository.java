package com.shelve.records.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.records.entity.RecordLevel;

public interface RecordLevelRepository
    extends JpaRepository<RecordLevel, Long>, JpaSpecificationExecutor<RecordLevel> {}
