package com.shelve.mails.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.mails.entity.MailPriority;

public interface MailPriorityRepository
    extends JpaRepository<MailPriority, Long>, JpaSpecificationExecutor<MailPriority> {}
