package com.shelve.settings.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.settings.entity.SettingCategory;

public interface SettingCategoryRepository
    extends JpaRepository<SettingCategory, Long>, JpaSpecificationExecutor<SettingCategory> {}
