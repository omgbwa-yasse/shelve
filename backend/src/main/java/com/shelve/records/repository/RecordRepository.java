package com.shelve.records.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.records.entity.Record;

public interface RecordRepository
    extends JpaRepository<Record, Long>, JpaSpecificationExecutor<Record> {}
