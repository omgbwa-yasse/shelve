package com.shelve.mails.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.mails.entity.MailTypology;

public interface MailTypologyRepository
    extends JpaRepository<MailTypology, Long>, JpaSpecificationExecutor<MailTypology> {}
