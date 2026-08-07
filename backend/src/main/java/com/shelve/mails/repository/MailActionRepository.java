package com.shelve.mails.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.mails.entity.MailAction;

public interface MailActionRepository
    extends JpaRepository<MailAction, Long>, JpaSpecificationExecutor<MailAction> {}
