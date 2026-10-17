package com.shelve.referentials.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.LawType;

public interface LawTypeRepository
    extends JpaRepository<LawType, Long>, JpaSpecificationExecutor<LawType> {}
