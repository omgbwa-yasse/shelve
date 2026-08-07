package com.shelve.referentials.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.Sort;

public interface SortRepository extends JpaRepository<Sort, Long>, JpaSpecificationExecutor<Sort> {}
