package com.shelve.dolly.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.dolly.entity.Dolly;

public interface DollyRepository
    extends JpaRepository<Dolly, Long>, JpaSpecificationExecutor<Dolly> {}
