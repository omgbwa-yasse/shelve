package com.shelve.localisation.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.localisation.entity.Shelf;

public interface ShelfRepository
    extends JpaRepository<Shelf, Long>, JpaSpecificationExecutor<Shelf> {}
