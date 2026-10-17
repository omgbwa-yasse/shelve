package com.shelve.collaboration.repository;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.collaboration.entity.WorkplaceCategory;

public interface WorkplaceCategoryRepository
    extends JpaRepository<WorkplaceCategory, Long>, JpaSpecificationExecutor<WorkplaceCategory> {
  Optional<WorkplaceCategory> findByCode(String code);
}
