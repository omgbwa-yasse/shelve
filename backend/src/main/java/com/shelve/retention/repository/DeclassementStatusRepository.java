package com.shelve.retention.repository;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.DeclassementStatus;

public interface DeclassementStatusRepository
    extends JpaRepository<DeclassementStatus, Long>, JpaSpecificationExecutor<DeclassementStatus> {
  Optional<DeclassementStatus> findByName(String name);
}
