package com.shelve.localisation.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.localisation.entity.Building;

public interface BuildingRepository
    extends JpaRepository<Building, Long>, JpaSpecificationExecutor<Building> {}
