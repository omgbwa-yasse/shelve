package com.shelve.retention.repository;

import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.DeclassementRecord;

public interface DeclassementRecordRepository
    extends JpaRepository<DeclassementRecord, Long>, JpaSpecificationExecutor<DeclassementRecord> {
  List<DeclassementRecord> findByDeclassementListId(Long declassementListId);

  Optional<DeclassementRecord> findByDeclassementListIdAndId(Long declassementListId, Long id);

  Optional<DeclassementRecord> findByDeclassementListIdAndRecordId(Long declassementListId, Long recordId);

  boolean existsByDeclassementListIdAndRecordId(Long declassementListId, Long recordId);
}
