package com.shelve.mails.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.mails.entity.Batch;

public interface BatchRepository
    extends JpaRepository<Batch, Long>, JpaSpecificationExecutor<Batch> {}
