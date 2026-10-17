package com.shelve.opac.repository;

import com.shelve.opac.entity.OpacPage;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;

public interface OpacPageRepository
    extends JpaRepository<OpacPage, Long>, JpaSpecificationExecutor<OpacPage> {
  List<OpacPage> findAllByOrderByIsDefaultDescTitleAsc();

  Optional<OpacPage> findBySlug(String slug);
}
