package com.shelve.organisation.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.organisation.entity.Organisation;

public interface OrganisationRepository
    extends JpaRepository<Organisation, Long>, JpaSpecificationExecutor<Organisation> {}
