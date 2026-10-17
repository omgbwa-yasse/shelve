package com.shelve.opac.repository;

import com.shelve.opac.entity.OpacPageVersion;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;

public interface OpacPageVersionRepository
    extends JpaRepository<OpacPageVersion, Long>, JpaSpecificationExecutor<OpacPageVersion> {
  List<OpacPageVersion> findAllByOpacPageIdOrderByIdDesc(Long pageId);

  Optional<OpacPageVersion> findFirstByOpacPageIdAndIsActiveTrue(Long pageId);

  Optional<OpacPageVersion> findFirstByOpacPageIdAndStatusOrderByIdDesc(Long pageId, String status);

  List<OpacPageVersion> findAllByOpacPageId(Long pageId);
}
