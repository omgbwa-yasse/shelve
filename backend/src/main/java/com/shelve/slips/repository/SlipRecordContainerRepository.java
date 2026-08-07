package com.shelve.slips.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.slips.entity.SlipRecordContainer;
import com.shelve.slips.entity.SlipRecordContainerId;

public interface SlipRecordContainerRepository
    extends JpaRepository<SlipRecordContainer, SlipRecordContainerId>,
        JpaSpecificationExecutor<SlipRecordContainer> {}
