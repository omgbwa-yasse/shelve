package com.shelve.settings.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.settings.entity.Setting;

public interface SettingRepository
    extends JpaRepository<Setting, Long>, JpaSpecificationExecutor<Setting> {}
