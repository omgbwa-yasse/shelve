package com.shelve.referentials.repository;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.ReferenceList;

public interface ReferenceListRepository
    extends JpaRepository<ReferenceList, Long>, JpaSpecificationExecutor<ReferenceList> {
  public Optional<ReferenceList> findByCode(String var1);

  public boolean existsByCode(String var1);

  public boolean existsByName(String var1);
}
