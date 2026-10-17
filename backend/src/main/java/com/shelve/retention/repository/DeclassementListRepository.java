package com.shelve.retention.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.DeclassementList;

public interface DeclassementListRepository
    extends JpaRepository<DeclassementList, Long>, JpaSpecificationExecutor<DeclassementList> {
  List<DeclassementList> findByOrganisationId(Long organisationId);
}
