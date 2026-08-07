package com.shelve.localisation.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.localisation.entity.ContainerProperty;

public interface ContainerPropertyRepository
    extends JpaRepository<ContainerProperty, Long>, JpaSpecificationExecutor<ContainerProperty> {}
