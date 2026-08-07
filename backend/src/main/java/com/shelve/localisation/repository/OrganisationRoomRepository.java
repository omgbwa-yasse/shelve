package com.shelve.localisation.repository;

import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;
import com.shelve.localisation.entity.OrganisationRoom;
import com.shelve.localisation.entity.OrganisationRoomId;

public interface OrganisationRoomRepository
    extends JpaRepository<OrganisationRoom, OrganisationRoomId> {
  public List<OrganisationRoom> findByIdRoomId(Long var1);

  public List<OrganisationRoom> findByIdOrganisationId(Long var1);

  public void deleteByIdRoomIdAndIdOrganisationId(Long var1, Long var2);

  public boolean existsById(OrganisationRoomId var1);
}
