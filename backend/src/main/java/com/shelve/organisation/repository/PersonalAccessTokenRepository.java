package com.shelve.organisation.repository;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.entity.PersonalAccessToken;

public interface PersonalAccessTokenRepository extends JpaRepository<PersonalAccessToken, Long> {
  public Optional<PersonalAccessToken> findById(Long var1);
}
