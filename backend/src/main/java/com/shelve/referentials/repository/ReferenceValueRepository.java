package com.shelve.referentials.repository;

import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.ReferenceValue;

public interface ReferenceValueRepository
    extends JpaRepository<ReferenceValue, Long>, JpaSpecificationExecutor<ReferenceValue> {
  public List<ReferenceValue> findByListId(Long var1);

  public long countByListId(Long var1);

  public Optional<ReferenceValue> findByListIdAndCode(Long var1, String var2);

  public Optional<ReferenceValue> findByListIdAndId(Long var1, Long var2);

  public boolean existsByListIdAndCode(Long var1, String var2);
}
