package com.shelve.records.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.records.entity.RecordConfidentiality;

public interface RecordConfidentialityRepository
    extends JpaRepository<RecordConfidentiality, Long>,
        JpaSpecificationExecutor<RecordConfidentiality> {}
