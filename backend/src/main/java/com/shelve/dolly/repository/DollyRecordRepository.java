package com.shelve.dolly.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import com.shelve.dolly.entity.Dolly;
import com.shelve.dolly.entity.DollyRecord;

public interface DollyRecordRepository extends JpaRepository<DollyRecord, Long> {
  public List<DollyRecord> findByDollyId(Long var1);

  public boolean existsByDollyIdAndRecordId(Long var1, Long var2);
}
