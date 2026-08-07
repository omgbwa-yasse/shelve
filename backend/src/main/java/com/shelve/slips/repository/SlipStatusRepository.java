package com.shelve.slips.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.slips.entity.SlipStatus;

public interface SlipStatusRepository
    extends JpaRepository<SlipStatus, Long>, JpaSpecificationExecutor<SlipStatus> {}
