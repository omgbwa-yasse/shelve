package com.shelve.slips.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.slips.entity.SlipRecord;

public interface SlipRecordRepository
    extends JpaRepository<SlipRecord, Long>, JpaSpecificationExecutor<SlipRecord> {}
