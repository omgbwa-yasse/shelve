package com.shelve.slips.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.slips.entity.Slip;

public interface SlipRepository extends JpaRepository<Slip, Long>, JpaSpecificationExecutor<Slip> {}
