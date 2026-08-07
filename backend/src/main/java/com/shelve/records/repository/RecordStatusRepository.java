package com.shelve.records.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.records.entity.RecordStatus;

public interface RecordStatusRepository
    extends JpaRepository<RecordStatus, Long>, JpaSpecificationExecutor<RecordStatus> {}
