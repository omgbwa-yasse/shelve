package com.shelve.referentials.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.Law;

public interface LawRepository
    extends JpaRepository<Law, Long>, JpaSpecificationExecutor<Law> {}
