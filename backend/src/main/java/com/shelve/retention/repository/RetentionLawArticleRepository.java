package com.shelve.retention.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.retention.entity.Retention;
import com.shelve.retention.entity.RetentionLawArticle;
import com.shelve.retention.entity.RetentionLawArticleId;

public interface RetentionLawArticleRepository
    extends JpaRepository<RetentionLawArticle, RetentionLawArticleId>,
        JpaSpecificationExecutor<RetentionLawArticle> {}
