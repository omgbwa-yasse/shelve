package com.shelve.communications.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.communications.entity.ReservationRecord;

public interface ReservationRecordRepository
    extends JpaRepository<ReservationRecord, Long>, JpaSpecificationExecutor<ReservationRecord> {}
