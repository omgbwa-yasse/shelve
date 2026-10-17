package com.shelve.referentials.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.MetadataDefinition;
import com.shelve.referentials.entity.RecordTypeMetadataProfile;

public interface RecordTypeMetadataProfileRepository
    extends JpaRepository<RecordTypeMetadataProfile, Long>,
        JpaSpecificationExecutor<RecordTypeMetadataProfile> {
  List<RecordTypeMetadataProfile> findByRecordTypeId(Long recordTypeId);

  List<RecordTypeMetadataProfile> findByMetadataDefinitionId(Long metadataDefinitionId);

  List<RecordTypeMetadataProfile> findByRecordTypeIdIn(List<Long> recordTypeIds);
}
