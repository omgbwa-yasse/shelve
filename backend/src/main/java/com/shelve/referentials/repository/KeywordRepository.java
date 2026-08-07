package com.shelve.referentials.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.Keyword;

public interface KeywordRepository
    extends JpaRepository<Keyword, Long>, JpaSpecificationExecutor<Keyword> {
  public List<Keyword> findTop10ByNameContainingIgnoreCaseOrderByNameAsc(String var1);

  public boolean existsByName(String var1);
}
