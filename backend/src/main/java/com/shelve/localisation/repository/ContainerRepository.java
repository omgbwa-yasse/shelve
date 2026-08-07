package com.shelve.localisation.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.localisation.entity.Container;

public interface ContainerRepository
    extends JpaRepository<Container, Long>, JpaSpecificationExecutor<Container> {}
