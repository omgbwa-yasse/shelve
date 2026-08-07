package com.shelve.mails.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.mails.entity.MailContainer;

public interface MailContainerRepository
    extends JpaRepository<MailContainer, Long>, JpaSpecificationExecutor<MailContainer> {}
