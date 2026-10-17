package com.shelve.referentials.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.referentials.entity.LawArticle;

public interface LawArticleRepository
    extends JpaRepository<LawArticle, Long>, JpaSpecificationExecutor<LawArticle> {
  List<LawArticle> findByLawId(Long lawId);
}
