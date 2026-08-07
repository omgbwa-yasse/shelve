package com.shelve.thesaurus.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import com.shelve.thesaurus.entity.ThesaurusConcept;

public interface ThesaurusConceptRepository
    extends JpaRepository<ThesaurusConcept, Long>, JpaSpecificationExecutor<ThesaurusConcept> {}
