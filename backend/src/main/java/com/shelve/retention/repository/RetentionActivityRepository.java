package com.shelve.retention.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.Retention;
import com.shelve.retention.entity.RetentionActivity;
import com.shelve.retention.entity.RetentionActivityId;

public interface RetentionActivityRepository
    extends JpaRepository<RetentionActivity, RetentionActivityId>,
        JpaSpecificationExecutor<RetentionActivity> {
  public List<RetentionActivity> findByIdRetentionId(Long var1);
}
