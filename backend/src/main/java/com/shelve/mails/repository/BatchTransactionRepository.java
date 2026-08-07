package com.shelve.mails.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.mails.entity.BatchTransaction;

public interface BatchTransactionRepository
    extends JpaRepository<BatchTransaction, Long>, JpaSpecificationExecutor<BatchTransaction> {}
