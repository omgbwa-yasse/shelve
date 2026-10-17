package com.shelve.opac.repository;

import com.shelve.opac.entity.OpacBlock;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface OpacBlockRepository extends JpaRepository<OpacBlock, Long> {
  List<OpacBlock> findAllByOpacPageVersionIdOrderByZoneAscOrderAsc(Long versionId);

  void deleteByOpacPageVersionId(Long versionId);
}
