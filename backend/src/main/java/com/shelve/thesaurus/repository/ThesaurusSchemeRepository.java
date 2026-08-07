package com.shelve.thesaurus.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.thesaurus.entity.ThesaurusScheme;

public interface ThesaurusSchemeRepository
    extends JpaRepository<ThesaurusScheme, Long>, JpaSpecificationExecutor<ThesaurusScheme> {}
