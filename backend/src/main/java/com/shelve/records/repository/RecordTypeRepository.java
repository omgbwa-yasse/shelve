package com.shelve.records.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.records.entity.RecordType;

public interface RecordTypeRepository
    extends JpaRepository<RecordType, Long>, JpaSpecificationExecutor<RecordType> {}
