package com.shelve.retention.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.DeclassementComment;

public interface DeclassementCommentRepository
    extends JpaRepository<DeclassementComment, Long>, JpaSpecificationExecutor<DeclassementComment> {
  List<DeclassementComment> findByDeclassementListIdOrderByCreatedAtAsc(Long declassementListId);
}
