package com.shelve.referentials.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.Activity;

public interface ActivityRepository
    extends JpaRepository<Activity, Long>, JpaSpecificationExecutor<Activity> {
  public List<Activity> findByParentIdIsNullOrderByCodeAsc();
}
