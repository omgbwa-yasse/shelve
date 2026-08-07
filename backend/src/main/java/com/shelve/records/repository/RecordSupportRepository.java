package com.shelve.records.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.records.entity.RecordSupport;

public interface RecordSupportRepository
    extends JpaRepository<RecordSupport, Long>, JpaSpecificationExecutor<RecordSupport> {}
