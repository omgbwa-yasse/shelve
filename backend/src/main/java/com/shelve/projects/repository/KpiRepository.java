package com.shelve.projects.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.projects.entity.Kpi;

public interface KpiRepository extends JpaRepository<Kpi, Long>, JpaSpecificationExecutor<Kpi> {}
