package com.shelve.referentials.repository;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.MetadataDefinition;

public interface MetadataDefinitionRepository
    extends JpaRepository<MetadataDefinition, Long>, JpaSpecificationExecutor<MetadataDefinition> {
  Optional<MetadataDefinition> findByCode(String code);
}
