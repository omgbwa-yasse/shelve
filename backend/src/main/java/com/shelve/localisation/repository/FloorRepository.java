package com.shelve.localisation.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.localisation.entity.Floor;

public interface FloorRepository
    extends JpaRepository<Floor, Long>, JpaSpecificationExecutor<Floor> {}
